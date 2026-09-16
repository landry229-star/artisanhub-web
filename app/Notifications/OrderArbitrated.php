<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderArbitrated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Décision d'arbitrage rendue — ArtisanHub")
            ->view('emails.orders.accepted', [
                'order'      => $this->order,
                'notifiable' => $notifiable,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $isArtisan = $notifiable->isArtisan();
        return [
            'icon'     => '⚖️',
            'title'    => "Décision d'arbitrage rendue",
            'message'  => "Une décision a été rendue sur le litige de la commande « {$this->order->title} ».",
            'url'      => $isArtisan
                ? route('artisan.orders.show', $this->order->id)
                : route('client.orders.show', $this->order->id),
            'order_id' => $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
