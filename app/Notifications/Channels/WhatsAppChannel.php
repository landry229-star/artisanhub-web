<?php
namespace App\Notifications\Channels;

use App\Services\WhatsAppSmsService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(private WhatsAppSmsService $service) {}

    public function send(object $notifiable, Notification $notification): void
    {
        $phone = $notifiable->phone ?? null;
        if (!$phone) return;

        // Chaque notification peut fournir un texte dédié via toWhatsApp().
        // À défaut, on retombe sur le contenu déjà préparé pour la cloche
        // (toDatabase) pour éviter de dupliquer le texte partout.
        if (method_exists($notification, 'toWhatsApp')) {
            $text = $notification->toWhatsApp($notifiable);
        } elseif (method_exists($notification, 'toDatabase')) {
            $data = $notification->toDatabase($notifiable);
            $text = trim(($data['icon'] ?? '') . ' ' . ($data['title'] ?? '') . "\n" . ($data['message'] ?? ''));
        } else {
            return;
        }

        $this->service->send($this->toE164($phone), $text);
    }

    /** Normalise un numéro béninois local (ex: 61 89 69 88) au format E.164 (+229...). */
    private function toE164(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '229')) return '+' . $digits;
        return '+229' . ltrim($digits, '0');
    }
}
