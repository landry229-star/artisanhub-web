<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompleted extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return $notifiable->orderNotificationChannels(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Commande terminée 🎉')
            ->view('emails.orders.completed', ['order' => $this->order, 'notifiable' => $notifiable]);
    }

    public function toDatabase(object $notifiable): array
    {
        $isArtisan = $notifiable->isArtisan();
        return [
            'icon'    => '🎉',
            'title'   => $isArtisan ? 'Paiement reçu !' : 'Commande terminée',
            'message' => $isArtisan
                ? "Vous avez reçu " . number_format($this->order->payment?->net_amount ?? 0, 0, ',', ' ') . " XOF pour « {$this->order->title} »"
                : "Votre commande « {$this->order->title} » est terminée. Laissez un avis !",
            'url'     => $isArtisan
                ? route('artisan.orders.show', $this->order->id)
                : route('client.orders.show', $this->order->id),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
