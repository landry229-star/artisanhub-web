<?php
namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderAccepted;
use App\Notifications\OrderStarted;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderCompleted;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderDisputed;
use App\Notifications\OrderArbitrated;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /** Commande passée → notifier l'artisan */
    public function orderPlaced(Order $order): void
    {
        $this->notify($order->artisan, new OrderPlaced($order));
    }

    /** Devis proposé/contre-proposé → notifier l'autre partie */
    public function quoteProposed(Order $order, \App\Models\Quote $quote): void
    {
        $recipient = $quote->proposed_by_role === 'artisan' ? $order->client : $order->artisan;
        $this->notify($recipient, new \App\Notifications\QuoteProposed($order, $quote));
    }

    /** Montant de devis anormal (écart fort vs catalogue ou round précédent) → admin */
    public function priceAnomalyDetected(Order $order, \App\Models\Quote $quote): void
    {
        User::where('role', 'admin')->each(
            fn(User $admin) => $this->notify($admin, new \App\Notifications\QuotePriceAnomaly($order, $quote))
        );
    }

    /** Commande acceptée → notifier le client */
    public function orderAccepted(Order $order): void
    {
        $this->notify($order->client, new OrderAccepted($order));
    }

    /** Travail démarré → notifier le client */
    public function orderStarted(Order $order): void
    {
        $this->notify($order->client, new OrderStarted($order));
    }

    /** Commande livrée → notifier le client */
    public function orderDelivered(Order $order): void
    {
        $this->notify($order->client, new OrderDelivered($order));
    }

    /** Commande terminée & payée → notifier artisan + client */
    public function orderCompleted(Order $order): void
    {
        $this->notify($order->artisan, new OrderCompleted($order));
        $this->notify($order->client,  new OrderCompleted($order));
    }

    /** Commande annulée → notifier l'autre partie */
    public function orderCancelled(Order $order): void
    {
        $userId = auth()->id();

        if ($userId === $order->client_id) {
            $recipient = $order->artisan;
        } elseif ($userId === $order->artisan_id) {
            $recipient = $order->client;
        } else {
            $recipient = $order->client ?? $order->artisan;
        }

        $this->notify($recipient, new OrderCancelled($order));
    }

    /** Litige → notifier tous les admins */
    public function orderDisputed(Order $order): void
    {
        User::where('role', 'admin')->each(
            fn(User $admin) => $this->notify($admin, new OrderDisputed($order))
        );
    }

    /** Arbitrage → notifier artisan + client */
    public function orderArbitrated(Order $order): void
    {
        $this->notify($order->client,  new OrderArbitrated($order));
        $this->notify($order->artisan, new OrderArbitrated($order));
    }

    /** Remboursement validé par un admin (arbitrage) → notifier le client */
    public function refundRequested(Order $order, \App\Models\Payment $payment): void
    {
        $this->notify($order->client, new \App\Notifications\RefundRequested($order, $payment));
    }

    // ── Helper privé ──────────────────────────────────────────────────────────
    private function notify(User $user, $notification): void
    {
        try {
            $user->notify($notification);
        } catch (\Exception $e) {
            // Ne jamais bloquer le flux principal à cause d'un email raté
            Log::error("Notification échouée [{$user->email}] : " . $e->getMessage());
        }
    }
}
