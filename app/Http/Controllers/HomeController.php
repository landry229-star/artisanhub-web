<?php
namespace App\Http\Controllers;

use App\Models\ArtisanProfile;
use App\Models\GuaranteeClaim;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        // Statistiques de confiance affichées sur la home — ce qui différencie
        // réellement ArtisanHub des annuaires classiques (voir ArtisanProfile::TIER_*).
        $trust = [
            'artisans_actifs'   => User::where('role', 'artisan')->where('is_active', true)->count(),
            'artisans_experts'  => ArtisanProfile::where('tier', ArtisanProfile::TIER_EXPERT)->count(),
            'artisans_verifies' => User::where('role', 'artisan')->where('is_verified', true)->count(),
            'garanties_traitees'=> GuaranteeClaim::whereIn('status', ['approved', 'rejected'])->count(),
        ];

        return view('welcome', compact('trust'));
    }
}
