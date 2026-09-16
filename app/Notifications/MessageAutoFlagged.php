<?php
namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MessageAutoFlagged extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    /**
     * Uniquement 'database' (pas de mail) : un message suspect est un signal
     * de modération interne, pas une urgence qui justifie une notification
     * email à chaque admin à chaque fois. Ça évite le bruit / la fatigue
     * d'alerte si plusieurs messages suspects arrivent dans la journée.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'     => '⚠️',
            'title'    => 'Message suspect détecté',
            'message'  => "Raison : {$this->message->flag_reason}",
            'url'      => route('admin.messages.show', $this->message->order_id),
            'order_id' => $this->message->order_id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
