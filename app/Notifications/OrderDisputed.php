<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDisputed extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Litige signalé — Intervention requise')
            ->view('emails.orders.disputed', ['order' => $this->order, 'notifiable' => $notifiable]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '⚠️',
            'title'   => 'Litige signalé',
            'message' => "Un litige a été ouvert sur la commande « {$this->order->title} »",
            'url'     => route('admin.orders.index') . '?status=litige',
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
