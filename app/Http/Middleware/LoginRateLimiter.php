<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginRateLimiter
{
    const MAX_ATTEMPTS  = 5;    // tentatives max
    const DECAY_MINUTES = 15;   // blocage en minutes

    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('POST') && $this->isLoginRequest($request)) {
            $key  = 'login_attempts:' . $this->getKey($request);
            $data = Cache::get($key);

            if ($data && $data['count'] >= self::MAX_ATTEMPTS) {
                // On stocke nous-mêmes l'échéance dans la valeur en cache,
                // au lieu de dépendre d'une méthode comme getTimeToLive()
                // qui n'existe sur aucun driver Cache standard de Laravel
                // (bug précédent : ça faisait planter l'app en 500 pile
                // au moment où la protection devait s'activer).
                $remaining = now()->diffInSeconds($data['expires_at'], false);

                if ($remaining > 0) {
                    $minutes = (int) ceil($remaining / 60);

                    Log::warning("Brute force détecté", [
                        'ip'    => $request->ip(),
                        'email' => $request->email,
                    ]);

                    return back()->withErrors([
                        'email' => "Trop de tentatives. Réessayez dans {$minutes} minute(s).",
                    ])->withInput($request->only('email'));
                }

                // Le blocage est en théorie expiré mais l'entrée cache traîne
                // encore (rare, tolérance d'horloge) → on la nettoie.
                Cache::forget($key);
            }
        }

        $response = $next($request);

        // Après la réponse : incrémenter si échec, réinitialiser si succès
        if ($request->isMethod('POST') && $this->isLoginRequest($request)) {
            $key = 'login_attempts:' . $this->getKey($request);

            if ($response->getStatusCode() === 302) {
                $session = $request->session();
                if ($session->has('errors')) {
                    // Échec → incrémenter
                    $existing = Cache::get($key);
                    $count    = ($existing['count'] ?? 0) + 1;

                    Cache::put($key, [
                        'count'      => $count,
                        'expires_at' => now()->addMinutes(self::DECAY_MINUTES),
                    ], now()->addMinutes(self::DECAY_MINUTES));
                } else {
                    // Succès → réinitialiser
                    Cache::forget($key);
                }
            }
        }

        return $response;
    }

    private function getKey(Request $request): string
    {
        // Clé basée sur IP + email pour éviter le blocage global
        return sha1($request->ip() . '|' . strtolower($request->email ?? ''));
    }

    private function isLoginRequest(Request $request): bool
    {
        return $request->routeIs('login') || $request->is('connexion');
    }
}
