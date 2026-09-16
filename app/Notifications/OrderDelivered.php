<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDelivered extends Notification implements ShouldQueue
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
        return "📦 Votre commande « {$this->order->title} » a été marquée comme livrée. Validez-la sur ArtisanHub pour débloquer le paiement.";
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre commande est prête 📦')
            ->view('emails.orders.delivered', ['order' => $this->order, 'notifiable' => $notifiable]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '📦',
            'title'   => 'Commande livrée — À valider',
            'message' => "« {$this->order->title} » est prête. Validez pour payer l'artisan.",
            'url'     => route('client.orders.show', $this->order->id),
            'order_id'=> $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
