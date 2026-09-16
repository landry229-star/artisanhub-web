<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Auth;

class DeliveryChoiceController extends Controller
{
    public function __construct(private DeliveryService $deliveryService) {}

    /** Le client demande un livreur précis (déjà connu) pour sa livraison en attente. */
    public function request(Delivery $delivery, User $livreur)
    {
        abort_if($delivery->order->client_id !== Auth::id(), 403);
        abort_if($livreur->role !== 'livreur', 404);
        abort_unless(
            Delivery::whereHas('order', fn ($q) => $q->where('client_id', Auth::id()))
                ->where('livreur_id', $livreur->id)->exists(),
            403,
            'Ce livreur ne fait pas partie de vos contacts.'
        );

        $ok = $this->deliveryService->requestSpecific($delivery, $livreur);

        return back()->with($ok ? 'success' : 'error', $ok
            ? "Demande envoyée à {$livreur->name} pour votre livraison."
            : "Ce livreur n'est plus disponible, ou la livraison ne peut plus changer de livreur.");
    }
}
