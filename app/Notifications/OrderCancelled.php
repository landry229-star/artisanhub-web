<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelled extends Notification implements ShouldQueue
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
            ->subject('Commande annulée sur ArtisanHub')
            ->view('emails.orders.cancelled', [
                'order'      => $this->order,
                'notifiable' => $notifiable,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $isArtisan = $notifiable->isArtisan();
        return [
            'icon'     => '❌',
            'title'    => 'Commande annulée',
            'message'  => "La commande « {$this->order->title} » a été annulée."
                . ($this->order->cancellation_reason || $this->order->rejection_reason
                    ? ' Motif : ' . ($this->order->cancellation_reason ?? $this->order->rejection_reason)
                    : ''),
            'url'      => $isArtisan
                ? route('artisan.orders.show', $this->order->id)
                : route('client.orders.show', $this->order->id),
            'order_id' => $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
