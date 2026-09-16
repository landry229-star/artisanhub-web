<?php
namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DeliveryAssigned;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DeliveryService
{
    /**
     * Déclenché quand l'artisan démarre le travail.
     * Cherche un livreur disponible dans la ville du client.
     */
    public function assignForOrder(Order $order): ?Delivery
    {
        // Si la commande ne nécessite pas de livraison → on skip
        if (!$order->needs_delivery) {
            return null;
        }

        $delivery = DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()
                ->with('client', 'artisan')->firstOrFail();
            $existing = Delivery::where('order_id', $lockedOrder->id)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            $deliveryCity = $lockedOrder->delivery_city ?? $lockedOrder->client->city;
            [$pickupLat, $pickupLng] = $this->coordinatesFor($lockedOrder->artisan->city);
            [$deliveryLat, $deliveryLng] = $this->coordinatesFor($deliveryCity);

            $proofCode = (string) random_int(100000, 999999);
            $delivery = Delivery::create([
                'order_id'         => $lockedOrder->id,
                'status'           => 'en_recherche',
                'pickup_city'      => $lockedOrder->artisan->city,
                'delivery_city'    => $deliveryCity,
                'pickup_address'   => $lockedOrder->artisan->city . ', Bénin',
                'delivery_address' => $lockedOrder->client->delivery_address ?? $deliveryCity . ', Bénin',
                'pickup_lat'       => $pickupLat,
                'pickup_lng'       => $pickupLng,
                'delivery_lat'     => $deliveryLat,
                'delivery_lng'     => $deliveryLng,
                'fee'              => $this->calculateFee($lockedOrder->artisan->city, $deliveryCity),
                'proof_code_hash'  => Hash::make($proofCode),
                'proof_code'       => $proofCode,
            ]);
            $lockedOrder->update(['delivery_id' => $delivery->id]);
            return $delivery;
        });
        if ($delivery->status !== 'en_recherche' || $delivery->livreur_id) {
            return $delivery;
        }

        // Chercher le meilleur livreur disponible
        $deliveryCity = $delivery->delivery_city;
        $livreur = $this->findAvailableLivreur($deliveryCity, $order->artisan->city);

        if ($livreur) {
            $delivery->update([
                'livreur_id' => $livreur->id,
                'status'     => 'assignee',
            ]);

            // Notifier le livreur
            try {
                $livreur->notify(new DeliveryAssigned($delivery));
                Log::info("Livreur {$livreur->name} assigné à la commande #{$order->id}");
            } catch (\Exception $e) {
                Log::error("Notification livreur échouée : " . $e->getMessage());
            }
        } else {
            Log::warning("Aucun livreur disponible à {$deliveryCity} pour la commande #{$order->id}");
        }

        return $delivery;
    }

    /**
     * Cherche le livreur disponible le plus proche (même ville, sinon ville artisan).
     */
    public function findAvailableLivreur(string $clientCity, string $artisanCity): ?User
    {
        // 1. Livreur dans la ville du CLIENT (priorité)
        $livreur = User::where('role', 'livreur')
            ->where('is_active', true)
            ->where('is_livreur_available', true)
            ->where('city', $clientCity)
            ->inRandomOrder()
            ->first();

        if ($livreur) return $livreur;

        // 2. Livreur dans la ville de l'ARTISAN
        $livreur = User::where('role', 'livreur')
            ->where('is_active', true)
            ->where('is_livreur_available', true)
            ->where('city', $artisanCity)
            ->inRandomOrder()
            ->first();

        if ($livreur) return $livreur;

        // 3. N'importe quel livreur disponible (fallback)
        return User::where('role', 'livreur')
            ->where('is_active', true)
            ->where('is_livreur_available', true)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Coordonnées approximatives d'une commune (pour centrer la carte de
     * suivi). Retourne [null, null] si la ville n'est pas reconnue.
     */
    public function coordinatesFor(string $city): array
    {
        static $coords = null;
        $coords ??= require config_path('benin_villes_coords.php');
        return $coords[$city] ?? [null, null];
    }

    /**
     * Calcule les frais de livraison selon les villes.
     */
    public function calculateFee(string $pickupCity, string $deliveryCity): int
    {
        if ($pickupCity === $deliveryCity) {
            return 1_000; // Même ville → 1 000 XOF
        }

        // Villes proches de Cotonou
        $sudBenin = ['Cotonou', 'Porto-Novo', 'Abomey-Calavi', 'Ouidah', 'Lokossa', 'Abomey', 'Bohicon'];

        $pickupInSud   = in_array($pickupCity, $sudBenin);
        $deliveryInSud = in_array($deliveryCity, $sudBenin);

        if ($pickupInSud && $deliveryInSud) {
            return 1_500; // Sud vers Sud → 1 500 XOF
        }

        return 3_000; // Longue distance → 3 000 XOF
    }

    /**
     * Le client "appelle" directement un livreur déjà connu (historique)
     * pour sa livraison en attente, au lieu de laisser le système en
     * choisir un au hasard. Ne fonctionne que si la livraison n'a pas
     * encore été récupérée (on peut encore changer de livreur).
     */
    public function requestSpecific(Delivery $delivery, User $livreur): bool
    {
        if (!in_array($delivery->status, ['en_recherche', 'assignee', 'echouee'])) {
            return false;
        }
        if ($livreur->role !== 'livreur' || !$livreur->is_active || !$livreur->is_livreur_available) {
            return false;
        }
        $order = $delivery->order()->first();
        if (!$order || !Delivery::whereHas('order', fn ($q) => $q->where('client_id', $order->client_id))
            ->where('livreur_id', $livreur->id)->exists()) {
            return false;
        }

        $updated = DB::transaction(function () use ($delivery, $livreur) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            if (!in_array($locked->status, ['en_recherche', 'assignee', 'echouee'], true)) {
                return false;
            }
            $locked->update(['livreur_id' => $livreur->id, 'status' => 'assignee']);
            $delivery->refresh();
            return true;
        });
        if (!$updated) return false;

        try {
            $livreur->notify(new DeliveryAssigned($delivery));
        } catch (\Exception $e) {
            Log::error("Notification livreur demandé directement échouée : " . $e->getMessage());
        }

        return true;
    }

    /**
     * Réassigner si le livreur refuse ou ne répond pas.
     * Exclut TOUS les livreurs déjà écartés pour cette livraison (pas
     * seulement le dernier), pour éviter de reproposer la mission à
     * quelqu'un qui l'a déjà refusée.
     */
    public function reassign(Delivery $delivery): void
    {
        [$livreur, $excluded] = DB::transaction(function () use ($delivery) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            $excluded = is_array($locked->excluded_livreur_ids) ? $locked->excluded_livreur_ids : [];
            if ($locked->livreur_id && !in_array($locked->livreur_id, $excluded, true)) {
                $excluded[] = $locked->livreur_id;
            }

            $candidateCities = array_values(array_unique(array_filter([
                $locked->delivery_city,
                $locked->pickup_city,
                $locked->order?->artisan?->city,
                $locked->order?->client?->city,
                'Cotonou',
            ], fn ($city) => filled($city))));

            $livreur = null;
            foreach ($candidateCities as $city) {
                $livreur = User::where('role', 'livreur')
                    ->where('is_active', true)
                    ->where('is_livreur_available', true)
                    ->where('city', $city)
                    ->whereNotIn('id', $excluded)
                    ->inRandomOrder()
                    ->first();

                if ($livreur) {
                    break;
                }
            }

            if (!$livreur) {
                $livreur = User::where('role', 'livreur')
                    ->where('is_active', true)
                    ->where('is_livreur_available', true)
                    ->whereNotIn('id', $excluded)
                    ->inRandomOrder()
                    ->first();
            }

            if ($livreur) {
                $locked->update([
                    'livreur_id'           => $livreur->id,
                    'status'               => 'assignee',
                    'excluded_livreur_ids' => $excluded,
                ]);
            } else {
                $locked->update(['status' => 'echouee', 'excluded_livreur_ids' => $excluded]);
            }
            $delivery->refresh();
            return [$livreur, $excluded];
        });

        if ($livreur) {
            try {
                $livreur->notify(new DeliveryAssigned($delivery));
            } catch (\Exception $e) {
                Log::error("Notification réassignation échouée : " . $e->getMessage());
            }
        } else {
            Log::warning("Aucun livreur disponible pour réassigner la livraison #{$delivery->id} (déjà exclus : " . implode(',', $excluded) . ")");
        }
    }
}
