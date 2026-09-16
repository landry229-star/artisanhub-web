<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $deliveries = Delivery::where('livreur_id', $user->id)
            ->with('order.client', 'order.artisan')
            ->latest('delivered_at')
            ->limit(50)
            ->get();

        $completedDeliveries = Delivery::where('livreur_id', $user->id)
            ->where('status', 'livree')
            ->get();

        $stats = [
            'total_earned' => (int) $completedDeliveries->sum('fee'),
            'month_earned' => (int) $completedDeliveries
                ->whereBetween('delivered_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('fee'),
            'deliveries_count' => $completedDeliveries->count(),
            'active_value' => (int) Delivery::where('livreur_id', $user->id)
                ->whereIn('status', ['acceptee', 'en_route', 'recuperee'])
                ->sum('fee'),
        ];

        return view('livreur.wallet.index', compact('user', 'deliveries', 'stats'));
    }

    public function statement()
    {
        $user = Auth::user();
        $deliveries = Delivery::where('livreur_id', $user->id)
            ->with('order.client', 'order.artisan')
            ->where('status', 'livree')
            ->latest('delivered_at')
            ->get();

        return Pdf::loadView('livreur.wallet.statement', compact('user', 'deliveries'))
            ->setPaper('a4', 'portrait')
            ->download('releve-gains-livreur-' . now()->format('Y-m') . '.pdf');
    }
}
