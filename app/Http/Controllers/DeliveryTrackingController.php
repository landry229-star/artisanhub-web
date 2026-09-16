<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DeliveryTrackingController extends Controller
{
    /**
     * Position en direct du livreur + statut de la livraison, pour le
     * client et l'artisan (carte de suivi). Interrogé en JS toutes les
     * ~10 secondes tant que la livraison est active.
     */
    public function show(Order $order)
    {
        abort_if(!in_array(Auth::id(), $order->chatParticipantIds()), 403);

        $delivery = $order->delivery;
        abort_if(!$delivery, 404);

        return response()->json([
            'status'       => $delivery->status,
            'status_label' => $delivery->statusLabel(),
            'trackable'    => $delivery->isTrackable(),
            'livreur_lat'  => $delivery->livreur_lat,
            'livreur_lng'  => $delivery->livreur_lng,
            'updated_at'   => $delivery->location_updated_at?->diffForHumans(),
            'pickup_lat'       => $delivery->pickup_lat,
            'pickup_lng'       => $delivery->pickup_lng,
            'delivery_lat'     => $delivery->delivery_lat,
            'delivery_lng'     => $delivery->delivery_lng,
            'livreur_name'     => $delivery->livreur?->name,
            'livreur_phone'    => $delivery->livreur?->phone,
        ]);
    }
}
