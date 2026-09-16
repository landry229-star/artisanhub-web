<?php
namespace App\Notifications;

use App\Models\GuaranteeClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuaranteeRefundCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public GuaranteeClaim $claim) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Remboursement effectué — ArtisanHub')
            ->line("Le remboursement de {$this->claim->refund_amount} XOF pour la commande #{$this->claim->order_id} a été effectué.")
            ->line('Vous devriez le recevoir sur votre compte Mobile Money sous peu si ce n\'est pas déjà fait.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'     => '✅',
            'title'    => 'Remboursement effectué',
            'message'  => "Remboursement de {$this->claim->refund_amount} XOF confirmé (commande #{$this->claim->order_id}).",
            'url'      => url('/client/commandes/' . $this->claim->order_id),
            'order_id' => $this->claim->order_id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
