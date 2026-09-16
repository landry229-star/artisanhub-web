<?php
namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'pending'   => Delivery::where('livreur_id', $user->id)->where('status','assignee')->count(),
            'active'    => Delivery::where('livreur_id', $user->id)->whereIn('status',['acceptee','en_route','recuperee'])->count(),
            'completed' => Delivery::where('livreur_id', $user->id)->where('status','livree')->count(),
            'earned'    => Delivery::where('livreur_id', $user->id)->where('status','livree')->sum('fee'),
        ];

        $missions = Delivery::where('livreur_id', $user->id)
            ->with('order.client', 'order.artisan')
            ->whereNotIn('status', ['livree','echouee'])
            ->latest()
            ->get();

        $history = Delivery::where('livreur_id', $user->id)
            ->with('order.client')
            ->whereIn('status', ['livree','echouee'])
            ->latest('delivered_at')
            ->take(10)
            ->get();

        return view('livreur.dashboard.index', compact('user','stats','missions','history'));
    }

    public function toggleAvailability()
    {
        $user = Auth::user();
        $user->update(['is_livreur_available' => !$user->is_livreur_available]);
        $status = $user->is_livreur_available ? 'Disponible' : 'Indisponible';
        return back()->with('success', "Statut mis à jour : {$status}");
    }
}
