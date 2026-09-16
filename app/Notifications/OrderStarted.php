<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStarted extends Notification implements ShouldQueue
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
            ->subject('L\'artisan a démarré votre commande 🔨')
            ->view('emails.orders.started', ['order' => $this->order, 'notifiable' => $notifiable]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '🔨',
            'title'   => 'Travail démarré',
            'message' => "{$this->order->artisan->name} a commencé à travailler sur « {$this->order->title} »",
            'url'     => route('client.orders.show', $this->order->id),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
