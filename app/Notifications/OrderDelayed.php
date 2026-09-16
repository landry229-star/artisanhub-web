<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDelayed extends Notification implements ShouldQueue
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
            ->subject('Délai dépassé pour une commande ArtisanHub')
            ->line("La commande « {$this->order->title} » a dépassé le délai prévu.")
            ->action('Voir la commande', $notifiable->isArtisan()
                ? route('artisan.orders.show', $this->order->id)
                : route('client.orders.show', $this->order->id));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => '⏰',
            'title' => 'Délai dépassé',
            'message' => "La commande « {$this->order->title} » nécessite un suivi : son délai est dépassé.",
            'url' => $notifiable->isArtisan()
                ? route('artisan.orders.show', $this->order->id)
                : route('client.orders.show', $this->order->id),
            'order_id' => $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
