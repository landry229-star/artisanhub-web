<?php
namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Notifications\DeliveryStatusUpdated;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function __construct(private DeliveryService $deliveryService) {}

    /** Liste des missions du livreur */
    public function index()
    {
        $missions = Delivery::where('livreur_id', Auth::id())
            ->with('order.client', 'order.artisan')
            ->whereNotIn('status', ['livree', 'echouee'])
            ->latest()
            ->limit(100)
            ->get();

        $history = Delivery::where('livreur_id', Auth::id())
            ->with('order.client')
            ->whereIn('status', ['livree', 'echouee'])
            ->latest('delivered_at')
            ->take(10)
            ->get();

        return view('livreur.missions.index', compact('missions', 'history'));
    }

    /** Détail d'une mission */
    public function show(Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        $delivery->load('order.client', 'order.artisan');
        return view('livreur.missions.show', compact('delivery'));
    }

    /** Livreur accepte la mission */
    public function accept(Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_if(!$delivery->canBeAccepted(), 422);
        DB::transaction(function () use ($delivery) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeAccepted(), 422);
            $locked->update(['status' => 'acceptee', 'accepted_at' => now()]);
            $delivery->refresh();
        });
        $this->notifyParties($delivery, 'accepted');
        return back()->with('success', '✅ Mission acceptée ! Rendez-vous chez l\'artisan pour récupérer le colis.');
    }

    /** Livreur est en route vers l'artisan */
    public function enRoute(Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_if(!$delivery->canBeStarted(), 422);
        DB::transaction(function () use ($delivery) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeStarted(), 422);
            $locked->update(['status' => 'en_route']);
            $delivery->refresh();
        });
        return back()->with('success', '🚴 Vous êtes en route vers l\'artisan.');
    }

    /** Livreur a récupéré le colis chez l'artisan */
    public function pickup(Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_if(!$delivery->canBePickedUpFromArtisan(), 422);
        DB::transaction(function () use ($delivery) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBePickedUpFromArtisan(), 422);
            $locked->update(['status' => 'recuperee', 'picked_up_at' => now()]);
            $delivery->refresh();
        });
        $this->notifyParties($delivery, 'picked_up');
        return back()->with('success', '📦 Colis récupéré ! En route vers le client.');
    }

    /** Livreur a livré chez le client */
    public function deliver(Request $request, Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_if(!$delivery->canBeDelivered(), 422);
        $hasValidCode = filled($request->proof_code)
            && $delivery->proof_code_hash
            && \Illuminate\Support\Facades\Hash::check($request->proof_code, $delivery->proof_code_hash);
        abort_unless($hasValidCode || $delivery->proof_method === 'signature', 422, 'Le code de remise ou la signature est requis.');
        DB::transaction(function () use ($delivery) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeDelivered(), 422);
            $order = $locked->order()->lockForUpdate()->firstOrFail();
            abort_if(!$order->canBeDelivered(), 422);
            $locked->update([
                'status' => 'livree',
                'delivered_at' => now(),
                'proof_verified_at' => now(),
                'proof_method' => $delivery->proof_method === 'signature' ? 'signature' : 'code',
            ]);
            $order->update(['status' => \App\Models\Order::STATUS_DELIVERED]);
            $delivery->refresh();
        });
        $this->notifyParties($delivery, 'delivered');
        return back()->with('success', '🎉 Livraison effectuée ! Le client va valider et payer.');
    }

    /** Livreur signale un problème */
    public function fail(Request $request, Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_unless(in_array($delivery->status, ['assignee', 'acceptee', 'en_route', 'recuperee'], true), 422);
        $request->validate(['notes' => ['required', 'string', 'max:300']]);
        DB::transaction(function () use ($delivery, $request) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['assignee', 'acceptee', 'en_route', 'recuperee'], true), 422);
            $locked->update(['status' => 'echouee', 'notes' => $request->notes]);
            $delivery->refresh();
        });
        $this->deliveryService->reassign($delivery);
        $this->notifyParties($delivery, 'failed');
        return back()->with('warning', 'Problème signalé. Un autre livreur va être contacté.');
    }

    /** Livreur refuse la mission */
    public function refuse(Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_if(!$delivery->canBeAccepted(), 422);
        $this->deliveryService->reassign($delivery);
        return back()->with('info', 'Mission refusée. Un autre livreur va être contacté.');
    }

    private function notifyParties(Delivery $delivery, string $event): void
    {
        $delivery->load('order.client', 'order.artisan');
        try {
            $delivery->order->client->notify(new DeliveryStatusUpdated($delivery, $event));
            $delivery->order->artisan->notify(new DeliveryStatusUpdated($delivery, $event));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification livraison : ' . $e->getMessage());
        }
    }
}
