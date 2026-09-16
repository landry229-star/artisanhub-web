<?php
namespace App\Notifications;

use App\Models\GuaranteeClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GuaranteeClaimSubmitted extends Notification
{
    use Queueable;

    public function __construct(public GuaranteeClaim $claim) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'     => '🛡️',
            'title'    => 'Réclamation garantie reçue',
            'message'  => "Commande #{$this->claim->order_id} — " . str($this->claim->reason)->limit(80),
            'url'      => route('admin.guarantee-claims.index'),
            'order_id' => $this->claim->order_id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
