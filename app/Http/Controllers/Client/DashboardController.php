<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $stats = [
            'total'      => Order::where('client_id', $user->id)->count(),
            'in_progress'=> Order::where('client_id', $user->id)->whereIn('status', ['en_attente','acceptee','en_cours'])->count(),
            'completed'  => Order::where('client_id', $user->id)->where('status', 'terminee')->count(),
            'spent'      => \App\Models\Payment::where('status', 'completed')
                                 ->whereHas('order', fn ($q) => $q->where('client_id', $user->id))
                                 ->sum('amount'),
        ];
        $recentOrders = Order::where('client_id', $user->id)
            ->with('artisan.artisanProfile')
            ->latest()->take(5)->get();

        return view('client.dashboard', compact('user', 'stats', 'recentOrders'));
    }
}
