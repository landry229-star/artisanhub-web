<?php
namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public Payment $payment) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Remboursement effectué — ArtisanHub')
            ->line("Le remboursement de {$this->payment->amount} XOF pour la commande « {$this->order->title} » a été effectué.")
            ->line('Vous devriez le recevoir sur votre compte Mobile Money sous peu si ce n\'est pas déjà fait.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '✅',
            'title'   => 'Remboursement effectué',
            'message' => "Remboursement de {$this->payment->amount} XOF confirmé pour la commande « {$this->order->title} ».",
            'url'     => route('client.orders.show', $this->order),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
