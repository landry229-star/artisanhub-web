<?php
namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryAssigned extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public Delivery $delivery) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];
        if ($notifiable->phone && $notifiable->whatsapp_opt_in) $channels[] = 'whatsapp';
        return $channels;
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "🚴 Nouvelle mission de livraison disponible sur ArtisanHub. Connectez-vous pour l'accepter.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle mission de livraison 🚴')
            ->view('emails.deliveries.assigned', ['delivery' => $this->delivery, 'notifiable' => $notifiable]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'        => '🚴',
            'title'       => 'Nouvelle mission de livraison',
            'message'     => "{$this->delivery->pickup_city} → {$this->delivery->delivery_city} · " . number_format($this->delivery->fee, 0, ',', ' ') . " XOF",
            'url'         => route('livreur.dashboard'),
            'delivery_id' => $this->delivery->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
