<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    // Mail + database pour la cloche, + WhatsApp/SMS pour les artisans
    // qui ne consultent pas leur dashboard tous les jours.
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];
        if ($notifiable->phone && $notifiable->whatsapp_opt_in) {
            $channels[] = 'whatsapp';
        }
        return $notifiable->orderNotificationChannels($channels);
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "📦 Nouvelle demande sur ArtisanHub : « {$this->order->title} » de {$this->order->client->name}. Connectez-vous pour répondre.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle demande de commande — ArtisanHub')
            ->view('emails.orders.placed', [
                'order'      => $this->order,
                'notifiable' => $notifiable,
            ]);
    }

    // Données stockées en base pour la cloche
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '📦',
            'title'   => 'Nouvelle commande reçue',
            'message' => "« {$this->order->title} » de {$this->order->client->name}",
            'url'     => route('artisan.orders.show', $this->order->id),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
