<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderAccepted extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];
        if ($notifiable->phone && $notifiable->whatsapp_opt_in) $channels[] = 'whatsapp';
        return $notifiable->orderNotificationChannels($channels);
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "✅ Votre commande « {$this->order->title} » a été acceptée par l'artisan. Le contrat est disponible sur ArtisanHub.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre commande a été acceptée ✅')
            ->view('emails.orders.accepted', ['order' => $this->order, 'notifiable' => $notifiable]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '✅',
            'title'   => 'Commande acceptée',
            'message' => "{$this->order->artisan->name} a accepté votre commande « {$this->order->title} »",
            'url'     => route('client.orders.show', $this->order->id),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
