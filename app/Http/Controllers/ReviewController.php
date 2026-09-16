<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'comment'  => ['nullable', 'string', 'max:500'],
        ]);

        [$order, $review] = DB::transaction(function () use ($request) {
            $order = Order::whereKey($request->order_id)->lockForUpdate()->firstOrFail();
            abort_if($order->client_id !== Auth::id(), 403);
            abort_if($order->status !== Order::STATUS_COMPLETED, 422, 'Commande non terminée.');
            abort_if($order->review()->exists(), 422, 'Vous avez déjà laissé un avis.');
            return [$order, Review::create([
                'order_id'   => $order->id,
                'client_id'  => Auth::id(),
                'artisan_id' => $order->artisan_id,
                'rating'     => $request->rating,
                'comment'    => $request->comment,
            ])];
        });

        // Recalculer la note de l'artisan
        $order->artisan->artisanProfile?->recalculateRating();

        return back()->with('success', 'Merci pour votre avis !');
    }

    public function reply(Request $request, Review $review)
    {
        abort_if($review->artisan_id !== Auth::id(), 403);
        $request->validate(['reply' => ['required', 'string', 'max:300']]);
        $review->update(['artisan_reply' => $request->reply]);
        return back()->with('success', 'Réponse publiée.');
    }
}
