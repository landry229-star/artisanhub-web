<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderAlert extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Alerte sécurité sur votre commande — ArtisanHub')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Notre équipe a détecté un élément suspect dans la conversation liée à votre commande **{$this->order->title}**.")
            ->line("**Message de l'équipe ArtisanHub :**")
            ->line($this->order->alert_message)
            ->line("Pour votre sécurité, **ne communiquez jamais en dehors de la plateforme** et ne payez que via ArtisanHub.")
            ->action('Voir ma commande', url('/client/commandes/' . $this->order->id))
            ->line('En cas de doute, contactez notre support.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'icon'          => '⚠️',
            'title'         => 'Alerte sécurité',
            'message'       => "Élément suspect détecté dans la conversation de « {$this->order->title} ». Ne payez et n'échangez que via ArtisanHub.",
            'url'           => route('client.orders.show', $this->order->id),
            'order_id'      => $this->order->id,
            'order_title'   => $this->order->title,
            'alert_message' => $this->order->alert_message,
        ];
    }

    public function toArray($notifiable): array { return $this->toDatabase($notifiable); }
}
