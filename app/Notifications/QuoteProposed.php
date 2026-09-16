<?php
namespace App\Notifications;

use App\Models\Order;
use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteProposed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public Quote $quote) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];
        if ($notifiable->phone && $notifiable->whatsapp_opt_in) {
            $channels[] = 'whatsapp';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle proposition de prix — ArtisanHub')
            ->line("Une proposition de {$this->quote->formattedAmount()} a été faite sur la commande « {$this->order->title} ».")
            ->when($this->quote->message, fn($m) => $m->line("Message : \"{$this->quote->message}\""))
            ->action('Voir et répondre', route(
                $this->quote->proposed_by_role === 'artisan' ? 'client.orders.show' : 'artisan.orders.show',
                $this->order->id
            ));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'    => '💬',
            'title'   => 'Nouvelle proposition de prix',
            'message' => "{$this->quote->formattedAmount()} sur « {$this->order->title} »",
            'url'     => route(
                $this->quote->proposed_by_role === 'artisan' ? 'client.orders.show' : 'artisan.orders.show',
                $this->order->id
            ),
            'order_id' => $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
