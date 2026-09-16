<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Historique des personnes avec qui l'utilisateur a déjà travaillé :
 *   - le client voit les artisans et les livreurs déjà sollicités
 *   - l'artisan voit ses clients déjà servis
 *   - le livreur voit les clients déjà livrés
 * Sert de point de départ pour recontacter quelqu'un ou, pour un client,
 * demander directement un livreur déjà connu sur une livraison en attente.
 */
class ContactHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isClient()) {
            $artisans = User::whereIn('id', Order::where('client_id', $user->id)
                    ->whereNotNull('artisan_id')->select('artisan_id')->distinct())
                ->limit(100)->get();

            $livreurs = User::whereIn('id', Delivery::whereIn('order_id',
                    Order::where('client_id', $user->id)->select('id'))
                    ->whereNotNull('livreur_id')->select('livreur_id')->distinct())
                ->limit(100)->get();

            // Livraison(s) en attente d'un livreur — pour proposer d'en
            // "appeler" un directement depuis l'historique.
            $pendingDeliveries = Delivery::whereIn('order_id', Order::where('client_id', $user->id)->pluck('id'))
                ->whereIn('status', ['en_recherche', 'echouee', 'assignee'])
                ->with('order')
                ->limit(100)->get();

            return view('contacts.client', compact('artisans', 'livreurs', 'pendingDeliveries'));
        }

        if ($user->isArtisan()) {
            $clients = User::whereIn('id', Order::where('artisan_id', $user->id)
                    ->whereNotNull('client_id')->select('client_id')->distinct())
                ->limit(100)->get();

            return view('contacts.artisan', compact('clients'));
        }

        if ($user->isLivreur()) {
            $clientIds = Order::whereIn('id', Delivery::where('livreur_id', $user->id)->select('order_id'))
                ->whereNotNull('client_id')->select('client_id')->distinct();
            $clients = User::whereIn('id', $clientIds)->limit(100)->get();

            return view('contacts.livreur', compact('clients'));
        }

        abort(403);
    }
}
