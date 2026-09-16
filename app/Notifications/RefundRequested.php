<?php
namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public Payment $payment) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('💰 Remboursement en cours — ArtisanHub')
            ->line("Suite à l'arbitrage du litige sur la commande « {$this->order->title} », notre équipe a validé votre remboursement de {$this->payment->amount} XOF.")
            ->line('Le remboursement Mobile Money sera lancé par notre équipe et peut prendre quelques jours ouvrés.')
            ->line('Vous recevrez une confirmation dès que le remboursement sera effectué.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '💰',
            'title'   => 'Remboursement en cours',
            'message' => "Remboursement de {$this->payment->amount} XOF validé pour la commande « {$this->order->title} ».",
            'url'     => route('client.orders.show', $this->order),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
