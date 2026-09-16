<?php
namespace App\View\Composers;

use App\Models\GuaranteeClaim;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Calcule une seule fois les compteurs de badges du sidebar admin,
 * quelle que soit la page admin actuellement affichée.
 *
 * Avant : chaque contrôleur devait penser à recalculer et passer les
 * mêmes compteurs ('unverified', 'disputed_orders', 'flagged_msgs',
 * 'pending_guarantee_claims'...) dans son $stats, sinon le badge
 * correspondant disparaissait silencieusement sur cette page.
 *
 * Avec ce composer, le sidebar reçoit toujours 'sidebarBadges' rempli,
 * peu importe le contrôleur qui a rendu la page.
 *
 * Les compteurs sont mis en cache 60s : ce composer tourne à CHAQUE
 * requête admin (le sidebar est inclus partout), donc sans cache ce
 * seraient 4 COUNT() en plus sur chaque page admin. Un léger délai de
 * fraîcheur (jusqu'à 60s) est acceptable pour des badges de navigation —
 * ce ne sont pas des données critiques affichées ailleurs.
 */
class AdminSidebarComposer
{
    const CACHE_TTL_SECONDS = 60;

    public function compose(View $view): void
    {
        $badges = Cache::remember('admin.sidebar_badges', self::CACHE_TTL_SECONDS, function () {
            return [
                'unverified'      => User::where('role', 'artisan')->where('is_verified', false)->count(),
                'disputed_orders' => Order::where('status', 'litige')->count(),
                'flagged_msgs'    => Message::where('is_flagged', true)->count(),
                'pending_guarantee_claims' => GuaranteeClaim::where('status', 'pending')->count(),
            ];
        });

        $view->with('sidebarBadges', $badges);
    }
}
