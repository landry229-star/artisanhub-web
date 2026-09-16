<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuaranteeClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FedaPayService;

class GuaranteeClaimController extends Controller
{
    /** Liste des réclamations, en attente en premier */
    public function index()
    {
        $claims = GuaranteeClaim::with(['order', 'client', 'artisan'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20);

        $stats = [
            'pending'  => GuaranteeClaim::where('status', 'pending')->count(),
            'approved' => GuaranteeClaim::where('status', 'approved')->count(),
            'rejected' => GuaranteeClaim::where('status', 'rejected')->count(),
        ];

        return view('admin.guarantee_claims.index', compact('claims', 'stats'));
    }

    /** Approuver une réclamation → remboursement au client */
    public function approve(Request $request, GuaranteeClaim $claim)
    {
        abort_if(!$claim->isPending(), 422, 'Cette réclamation a déjà été traitée.');

        $request->validate([
            'refund_amount' => ['required', 'integer', 'min:1'],
            'admin_note'    => ['nullable', 'string', 'max:500'],
        ]);

        // Le remboursement ne peut pas dépasser ce qui a été mis de côté
        // dans le fonds de garantie pour cette commande précise.
        DB::transaction(function () use ($claim, $request) {
            $locked = GuaranteeClaim::whereKey($claim->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->isPending(), 422, 'Cette réclamation a déjà été traitée.');
            $payment = $locked->order()->firstOrFail()->payment()->lockForUpdate()->first();
            abort_if(!$payment, 422, 'Aucun paiement associé à cette réclamation.');
            $maxRefund = (int) $payment->guarantee_contribution;
            abort_if($request->refund_amount > $maxRefund,
                422, "Le remboursement ne peut pas dépasser {$maxRefund} XOF (fonds de garantie de cette commande).");
            $locked->update([
                'status'        => 'approved',
                'refund_amount' => $request->refund_amount,
                'admin_note'    => $request->admin_note,
                'resolved_by'   => Auth::id(),
                'resolved_at'   => now(),
            ]);
        });

        try {
            $claim->client->notify(new \App\Notifications\GuaranteeClaimResolved($claim));
        } catch (\Exception $e) {
            Log::error('Notification résolution garantie : ' . $e->getMessage());
        }

        return back()->with('success', "✅ Réclamation approuvée — {$request->refund_amount} XOF à rembourser au client.");
    }

    public function confirmRefund(GuaranteeClaim $claim)
    {
        abort_if(!$claim->refundIsPending(), 422, 'Aucun remboursement en attente pour cette réclamation.');

        $fedapay = app(FedaPayService::class);
        try {
            $payout = $claim->fedapay_payout_id
                ? $fedapay->retrievePayout($claim->fedapay_payout_id)
                : $fedapay->createRefundPayout(
                    (int) $claim->refund_amount,
                    $claim->client,
                    "refund-claim-{$claim->id}"
                );
        } catch (\FedaPay\Error\ApiConnection|\FedaPay\Error\InvalidRequest $e) {
            $claim->update(['refund_status' => 'failed', 'refund_error' => $e->getMessage()]);
            Log::error('Échec remboursement garantie FedaPay', ['claim_id' => $claim->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'FedaPay n’a pas pu lancer le remboursement. Vérifiez la configuration et réessayez.');
        }

        $claim->update([
            'fedapay_payout_id' => $claim->fedapay_payout_id ?: ($payout->id ?? null),
            'refund_status' => $payout->status ?? 'pending',
            'refund_error' => null,
        ]);

        if (!$fedapay->payoutSucceeded($payout)) {
            return back()->with('error', 'Le remboursement est encore en traitement par FedaPay. Vérifiez à nouveau son statut depuis cet écran.');
        }

        DB::transaction(function () use ($claim) {
            $locked = GuaranteeClaim::whereKey($claim->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->refundIsPending(), 422, 'Aucun remboursement en attente pour cette réclamation.');
            $locked->markRefunded(Auth::user());
        });

        try {
            $claim->client->notify(new \App\Notifications\GuaranteeRefundCompleted($claim));
        } catch (\Exception $e) {
            Log::error('Notification remboursement garantie confirmé : ' . $e->getMessage());
        }

        return back()->with('success', "✅ Remboursement de {$claim->refund_amount} XOF confirmé pour la commande #{$claim->order_id}.");
    }

    /** Rejeter une réclamation */
    public function reject(Request $request, GuaranteeClaim $claim)
    {
        abort_if(!$claim->isPending(), 422, 'Cette réclamation a déjà été traitée.');

        $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($claim, $request) {
            $locked = GuaranteeClaim::whereKey($claim->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->isPending(), 422, 'Cette réclamation a déjà été traitée.');
            $locked->update([
                'status'      => 'rejected',
                'admin_note'  => $request->admin_note,
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);
            $claim->refresh();
        });

        try {
            $claim->client->notify(new \App\Notifications\GuaranteeClaimResolved($claim));
        } catch (\Exception $e) {
            Log::error('Notification résolution garantie : ' . $e->getMessage());
        }

        return back()->with('success', 'Réclamation rejetée.');
    }
}
