<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuaranteeClaim;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GuaranteeClaimController extends Controller
{
    // Délai maximum pour réclamer après le paiement effectif de la commande.
    const CLAIM_WINDOW_DAYS = 14;

    /** Client soumet une réclamation "satisfait ou repris" */
    public function store(Request $request, Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        abort_if($order->status !== Order::STATUS_COMPLETED, 422,
            'Seules les commandes terminées peuvent faire l\'objet d\'une réclamation.');

        $payment = $order->payment;

        // L'éligibilité se base sur la contribution effectivement prélevée
        // au moment du paiement (et non sur le palier ACTUEL de l'artisan,
        // qui peut avoir changé depuis) — c'est ce qui a été promis au client.
        abort_if(!$payment || $payment->guarantee_contribution <= 0, 422,
            'Cette commande n\'est pas couverte par la garantie satisfait ou repris.');

        abort_if($order->guaranteeClaim()->exists(), 422,
            'Une réclamation existe déjà pour cette commande.');

        $daysSincePaid = $payment->paid_at ? now()->diffInDays($payment->paid_at) : 999;
        abort_if($daysSincePaid > self::CLAIM_WINDOW_DAYS, 422,
            'Le délai de réclamation (' . self::CLAIM_WINDOW_DAYS . ' jours après paiement) est dépassé.');

        $request->validate([
            'reason'   => ['required', 'string', 'max:1000'],
            'evidence' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $evidencePath = $request->hasFile('evidence')
            ? $request->file('evidence')->store('guarantee-evidence', 'local')
            : null;

        $claim = DB::transaction(function () use ($order, $request, $evidencePath) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if($lockedOrder->guaranteeClaim()->exists(), 422,
                'Une réclamation existe déjà pour cette commande.');
            return GuaranteeClaim::create([
                'order_id'      => $lockedOrder->id,
                'client_id'     => Auth::id(),
                'artisan_id'    => $lockedOrder->artisan_id,
                'reason'        => $request->reason,
                'evidence_path' => $evidencePath,
            ]);
        });

        // Notifier tous les admins immédiatement — une réclamation de
        // garantie touche à de l'argent, elle doit être traitée vite.
        User::where('role', 'admin')->chunkById(100, function ($admins) use ($claim) {
            $admins->each(function ($admin) use ($claim) {
            try {
                $admin->notify(new \App\Notifications\GuaranteeClaimSubmitted($claim));
            } catch (\Exception $e) {
                Log::error('Notification réclamation garantie : ' . $e->getMessage());
            }
            });
        });

        return back()->with('success',
            'Votre réclamation a été envoyée. Notre équipe l\'examine et vous répond sous 48h.');
    }
}
