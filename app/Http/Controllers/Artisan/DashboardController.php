<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ArtisanProfile;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $profile = $user->artisanProfile ?? ArtisanProfile::create([
            'user_id' => $user->id,
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);

        $stats = [
            'pending'    => Order::where('artisan_id', $user->id)->where('status', 'en_attente')->count(),
            'in_progress'=> Order::where('artisan_id', $user->id)->where('status', 'en_cours')->count(),
            'completed'  => Order::where('artisan_id', $user->id)->where('status', 'terminee')->count(),
            'revenue'    => \App\Models\Payment::where('status', 'completed')
                                 ->whereHas('order', fn ($q) => $q->where('artisan_id', $user->id))
                                 ->sum('net_amount'),
        ];

        $recentOrders = Order::where('artisan_id', $user->id)
            ->with('client')
            ->latest()
            ->take(5)
            ->get();

        return view('artisan.dashboard', compact('user', 'profile', 'stats', 'recentOrders'));
    }
}
