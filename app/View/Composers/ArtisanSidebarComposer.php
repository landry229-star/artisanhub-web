<?php

namespace App\View\Composers;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Calcule le nombre d'avis sans réponse pour le badge du sidebar artisan.
 * Même logique que AdminSidebarComposer : le sidebar est inclus sur
 * chaque page artisan, donc on met en cache 60s pour éviter un COUNT()
 * supplémentaire à chaque requête. Clé de cache par artisan (contrairement
 * au sidebar admin qui est global).
 */
class ArtisanSidebarComposer
{
    const CACHE_TTL_SECONDS = 60;

    public function compose(View $view): void
    {
        $artisanId = Auth::id();
        if (! $artisanId) {
            return;
        }

        $pendingReplies = Cache::remember("artisan.{$artisanId}.pending_review_replies", self::CACHE_TTL_SECONDS, function () use ($artisanId) {
            return Review::where('artisan_id', $artisanId)
                ->whereNull('artisan_reply')
                ->whereNotNull('comment')
                ->count();
        });

        $view->with('pendingReplies', $pendingReplies);
    }
}
