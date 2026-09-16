<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Traduction automatique des messages de la messagerie (texte et
 * transcriptions vocales) vers la langue préférée du destinataire.
 *
 * Le service est piloté par config('services.translation') et supporte
 * deux drivers :
 *   - 'anthropic'  : appelle l'API Anthropic (Claude) pour traduire.
 *   - 'libretranslate' : appelle une instance LibreTranslate (auto-hébergée
 *      ou publique) via son endpoint /translate.
 *   - 'none' (défaut) : traduction désactivée, retourne toujours null —
 *      la plateforme continue de fonctionner normalement sans traduction.
 *
 * Best-effort : toute erreur réseau/API est loguée et avalée, jamais
 * remontée à l'utilisateur (l'envoi du message ne doit jamais échouer
 * à cause d'une panne du service de traduction).
 */
class TranslationService
{
    /**
     * Traduit $text vers $targetLang.
     * Retourne null si le driver n'est pas configuré, si $targetLang est
     * identique à $sourceLang, ou en cas d'échec.
     */
    public function translate(string $text, string $targetLang, ?string $sourceLang = null): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if ($sourceLang && $sourceLang === $targetLang) {
            return null;
        }

        $driver = config('services.translation.driver', 'none');

        try {
            return match ($driver) {
                'anthropic'       => $this->viaAnthropic($text, $targetLang),
                'libretranslate'  => $this->viaLibreTranslate($text, $targetLang),
                default           => null,
            };
        } catch (\Throwable $e) {
            Log::warning('TranslationService: échec de traduction ('.$driver.') : '.$e->getMessage());
            return null;
        }
    }

    private function viaAnthropic(string $text, string $targetLang): ?string
    {
        $key = config('services.translation.anthropic_key');
        if (!$key) {
            return null;
        }

        $response = Http::withHeaders([
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])
            ->timeout(15)
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-6',
                'max_tokens' => 1024,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => "Traduis le texte suivant en langue '{$targetLang}'. "
                        ."Réponds uniquement avec la traduction, sans commentaire ni guillemets.\n\n{$text}",
                ]],
            ]);

        if (!$response->successful()) {
            Log::warning('TranslationService (anthropic) : réponse HTTP '.$response->status());
            return null;
        }

        $blocks = $response->json('content', []);
        $out = collect($blocks)->firstWhere('type', 'text')['text'] ?? null;

        return $out ? trim($out) : null;
    }

    private function viaLibreTranslate(string $text, string $targetLang): ?string
    {
        $url = config('services.translation.libretranslate_url');
        if (!$url) {
            return null;
        }

        $response = Http::timeout(10)->asForm()->post(rtrim($url, '/').'/translate', [
            'q'      => $text,
            'source' => 'auto',
            'target' => $targetLang,
            'format' => 'text',
            'api_key'=> config('services.translation.libretranslate_key'),
        ]);

        if (!$response->successful()) {
            Log::warning('TranslationService (libretranslate) : réponse HTTP '.$response->status());
            return null;
        }

        return $response->json('translatedText');
    }
}
