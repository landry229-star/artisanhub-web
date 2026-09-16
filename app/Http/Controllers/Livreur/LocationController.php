<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Le livreur envoie sa position GPS courante pendant une mission
     * active (appelé périodiquement en JS via navigator.geolocation).
     */
    public function update(Request $request, Delivery $delivery)
    {
        abort_if($delivery->livreur_id !== Auth::id(), 403);
        abort_if(!$delivery->isTrackable(), 422, 'Cette mission n\'est plus suivie en direct.');

        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $delivery->update([
            'livreur_lat'          => $request->lat,
            'livreur_lng'          => $request->lng,
            'location_updated_at'  => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
