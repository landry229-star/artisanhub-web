<?php
namespace App\Notifications;

use App\Models\Order;
use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerte non bloquante envoyée aux admins quand un devis s'écarte fortement
 * d'une référence connue (prix catalogue du service, ou round précédent).
 * Voir Order::priceAnomalyWarning().
 */
class QuotePriceAnomaly extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public Quote $quote) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Écart de prix suspect sur un devis — ArtisanHub')
            ->line("Commande #{$this->order->id} « {$this->order->title} » : {$this->quote->price_anomaly_note}")
            ->line("Montant proposé : {$this->quote->formattedAmount()}")
            ->action('Voir la commande', route('admin.orders.index'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'     => '⚠️',
            'title'    => 'Écart de prix suspect sur un devis',
            'message'  => "Commande #{$this->order->id} : {$this->quote->price_anomaly_note}",
            'url'      => route('admin.orders.index'),
            'order_id' => $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
