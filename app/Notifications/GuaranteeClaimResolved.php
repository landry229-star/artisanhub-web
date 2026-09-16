<?php
namespace App\Notifications;

use App\Models\GuaranteeClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuaranteeClaimResolved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public GuaranteeClaim $claim) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];
        if ($notifiable->phone && $notifiable->whatsapp_opt_in) $channels[] = 'whatsapp';
        return $channels;
    }

    public function toWhatsApp(object $notifiable): string
    {
        return $this->claim->isApproved()
            ? "✅ Votre réclamation de garantie (commande #{$this->claim->order_id}) est approuvée — {$this->claim->refund_amount} XOF vous seront remboursés."
            : "Réponse à votre réclamation de garantie (commande #{$this->claim->order_id}) : non retenue. Détails sur ArtisanHub.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->greeting("Bonjour {$notifiable->name},");

        if ($this->claim->isApproved()) {
            $mail->subject('✅ Votre réclamation a été approuvée — ArtisanHub')
                ->line("Votre réclamation concernant la commande #{$this->claim->order_id} a été approuvée.")
                ->line("Montant à vous rembourser : {$this->claim->refund_amount} XOF.")
                ->line('Notre équipe va procéder au remboursement sous peu.');
        } else {
            $mail->subject('Réponse à votre réclamation — ArtisanHub')
                ->line("Votre réclamation concernant la commande #{$this->claim->order_id} n'a pas été retenue.")
                ->line("Raison : {$this->claim->admin_note}");
        }

        return $mail->action('Voir ma commande', url('/client/commandes/' . $this->claim->order_id));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'     => $this->claim->isApproved() ? '✅' : 'ℹ️',
            'title'    => $this->claim->isApproved() ? 'Réclamation approuvée' : 'Réclamation rejetée',
            'message'  => $this->claim->isApproved()
                ? "Remboursement de {$this->claim->refund_amount} XOF."
                : $this->claim->admin_note,
            'url'      => url('/client/commandes/' . $this->claim->order_id),
            'order_id' => $this->claim->order_id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
