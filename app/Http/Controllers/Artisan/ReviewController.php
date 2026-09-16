<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Vue consolidée de tous les avis reçus. Avant cette page, l'artisan
     * devait ouvrir chaque commande individuellement pour retrouver et
     * répondre à un avis — la fonctionnalité de réponse existait déjà
     * (ReviewController::reply) mais n'avait aucun point d'entrée central.
     */
    public function index()
    {
        $reviews = Review::where('artisan_id', Auth::id())
            ->with('client', 'order')
            ->latest()
            ->paginate(10);

        $withoutReply = Review::where('artisan_id', Auth::id())
            ->whereNull('artisan_reply')
            ->whereNotNull('comment')
            ->count();

        return view('artisan.reviews.index', compact('reviews', 'withoutReply'));
    }
}
