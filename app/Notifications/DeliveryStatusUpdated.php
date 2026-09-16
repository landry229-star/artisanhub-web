<?php
namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public Delivery $delivery, public string $event) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $subjects = [
            'accepted'  => 'Votre livraison est prise en charge 🚴',
            'picked_up' => 'L\'objet est récupéré — en route ! 📦',
            'delivered' => 'Livraison effectuée ✅',
            'failed'    => 'Problème de livraison ⚠️',
        ];

        return (new MailMessage)
            ->subject($subjects[$this->event] ?? 'Mise à jour livraison — ArtisanHub')
            ->view('emails.deliveries.status', [
                'delivery'   => $this->delivery,
                'event'      => $this->event,
                'notifiable' => $notifiable,
            ]);
    }
}
