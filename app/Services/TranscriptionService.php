<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Transcription des messages vocaux (speech-to-text), utilisée avant
 * traduction pour permettre "l'audio traduit dans la langue de l'autre".
 *
 * Piloté par config('services.transcription'). Driver par défaut 'none' :
 * la transcription est simplement absente et le vocal reste jouable tel
 * quel (aucune traduction n'est alors possible, mais l'envoi du vocal
 * fonctionne toujours).
 */
class TranscriptionService
{
    /** Retourne le texte transcrit, ou null si non configuré / échec. */
    public function transcribe(string $absolutePath): ?string
    {
        $driver = config('services.transcription.driver', 'none');

        try {
            return match ($driver) {
                'openai_whisper' => $this->viaOpenAiWhisper($absolutePath),
                default          => null,
            };
        } catch (\Throwable $e) {
            Log::warning('TranscriptionService: échec de transcription ('.$driver.') : '.$e->getMessage());
            return null;
        }
    }

    private function viaOpenAiWhisper(string $absolutePath): ?string
    {
        $key = config('services.transcription.openai_key');
        if (!$key || !file_exists($absolutePath)) {
            return null;
        }

        $response = Http::withToken($key)
            ->timeout(30)
            ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
            ]);

        if (!$response->successful()) {
            Log::warning('TranscriptionService (whisper) : réponse HTTP '.$response->status());
            return null;
        }

        return $response->json('text');
    }
}
