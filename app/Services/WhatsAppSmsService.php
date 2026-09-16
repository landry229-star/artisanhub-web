<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoi de messages WhatsApp / SMS aux utilisateurs à faible littératie
 * numérique (beaucoup d'artisans consultent WhatsApp bien plus souvent
 * que leur email ou le tableau de bord web).
 *
 * Pilotes disponibles (config/services.php → 'whatsapp.driver') :
 *   - 'log'      : n'envoie rien, écrit dans storage/logs/laravel.log (par défaut en dev)
 *   - 'meta'     : WhatsApp Cloud API (Meta) — nécessite WHATSAPP_TOKEN + WHATSAPP_PHONE_ID
 *   - 'sms'      : passerelle SMS générique par HTTP (ex. FedaPay SMS, Data354, Twilio...)
 *
 * Tant qu'aucune clé n'est configurée, le pilote retombe automatiquement
 * sur 'log' pour ne jamais bloquer un flux applicatif en développement.
 */
class WhatsAppSmsService
{
    public function send(string $phoneE164, string $message): bool
    {
        $driver = config('services.whatsapp.driver', 'log');

        try {
            return match ($driver) {
                'meta' => $this->sendViaMeta($phoneE164, $message),
                'sms'  => $this->sendViaGenericSms($phoneE164, $message),
                default => $this->sendViaLog($phoneE164, $message),
            };
        } catch (\Exception $e) {
            Log::error("WhatsApp/SMS envoi échoué [{$phoneE164}] : " . $e->getMessage());
            return false;
        }
    }

    private function sendViaLog(string $phone, string $message): bool
    {
        Log::info("[WHATSAPP/SMS SIMULÉ] → {$phone}");
        return true;
    }

    /** WhatsApp Cloud API (Meta) — https://developers.facebook.com/docs/whatsapp/cloud-api */
    private function sendViaMeta(string $phone, string $message): bool
    {
        $token   = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            Log::warning('WHATSAPP_TOKEN / WHATSAPP_PHONE_ID absents, repli sur le mode log.');
            return $this->sendViaLog($phone, $message);
        }

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/v20.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ]);

        return $response->successful();
    }

    /** Passerelle SMS générique — à adapter selon le fournisseur retenu au Bénin. */
    private function sendViaGenericSms(string $phone, string $message): bool
    {
        $url = config('services.whatsapp.sms_url');
        $key = config('services.whatsapp.sms_key');

        if (!$url || !$key) {
            Log::warning('SMS_URL / SMS_KEY absents, repli sur le mode log.');
            return $this->sendViaLog($phone, $message);
        }

        $response = Http::withToken($key)->post($url, [
            'to'   => $phone,
            'text' => $message,
        ]);

        return $response->successful();
    }
}
