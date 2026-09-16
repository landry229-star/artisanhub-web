<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FedaPayService;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        // ── KPIs plateforme ───────────────────────────────────────────────────
        $stats = [
            'total_commission'   => Payment::where('status','completed')->sum('commission'),
            'month_commission'   => Payment::where('status','completed')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at',  now()->year)
                ->sum('commission'),
            'total_volume'       => Payment::where('status','completed')->sum('amount'),
            'pending_payments'   => Payment::where('status','pending')->count(),
            'pending_reversals'  => Payment::where('status','completed')
                ->whereNull('reversed_at')->whereNull('refund_requested_at')->sum('net_amount'),
        ];

        // ── Reversements à faire (par artisan) ────────────────────────────────
        $pendingReversals = User::where('role','artisan')
            ->whereHas('ordersAsArtisan.payment', function ($q) {
                $q->where('status','completed')->whereNull('reversed_at')->whereNull('refund_requested_at');
            })
            ->with(['artisanProfile', 'ordersAsArtisan' => function ($q) {
                $q->whereHas('payment', fn($p) => $p->where('status','completed')->whereNull('reversed_at')->whereNull('refund_requested_at'))
                  ->with('payment');
            }])
            ->limit(100)
            ->get()
            ->map(function ($artisan) {
                $artisan->solde_a_reverser = $artisan->ordersAsArtisan
                    ->sum(fn($o) => $o->payment?->net_amount ?? 0);
                $artisan->nb_paiements = $artisan->ordersAsArtisan->count();
                return $artisan;
            })
            ->sortByDesc('solde_a_reverser');

        // ── Top artisans par revenus ───────────────────────────────────────────
        $topArtisans = User::where('role','artisan')
            ->with('artisanProfile')
            ->selectSub(
                Payment::query()->selectRaw('COALESCE(SUM(net_amount), 0)')
                    ->where('status', 'completed')
                    ->whereHas('order', fn ($q) => $q->whereColumn('artisan_id', 'users.id')),
                'total_earned'
            )
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();

        // ── Historique paiements ───────────────────────────────────────────────
        $payments = Payment::where('status','completed')
            ->with('order.client', 'order.artisan', 'reversedBy')
            ->latest('paid_at')
            ->paginate(20);

        // ── Remboursements en attente (arbitrage litige) ──────────────────────
        $pendingRefunds = Payment::where('status', 'completed')
            ->whereNotNull('refund_requested_at')
            ->whereNull('refunded_at')
            ->with('order.client', 'refundRequestedBy')
            ->latest('refund_requested_at')
            ->limit(100)
            ->get();

        return view('admin.wallet.index', compact(
            'stats', 'pendingReversals', 'topArtisans', 'payments', 'pendingRefunds'
        ));
    }

    /**
     * Admin marque tous les paiements en attente d'un artisan comme reversés.
     */
    public function reverse(Request $request, User $artisan)
    {
        $request->validate([
            'reversal_note' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if($artisan->role !== 'artisan', 422, 'Utilisateur invalide.');
        abort_if(!$artisan->isPhoneVerified(), 422, 'Le numéro Mobile Money de cet artisan doit être vérifié.');
        $amount = (int) Payment::whereHas('order', fn($q) => $q->where('artisan_id', $artisan->id))
            ->where('status', 'completed')->whereNull('reversed_at')->whereNull('refund_requested_at')
            ->sum('net_amount');
        abort_if($amount < 1, 422, 'Aucun montant disponible pour ce reversement.');

        try {
            $payout = app(FedaPayService::class)->createMobileMoneyPayout(
                $amount,
                $artisan,
                "payout-artisan-{$artisan->id}-" . now()->format('YmdHis')
            );
        } catch (\FedaPay\Error\ApiConnection|\FedaPay\Error\InvalidRequest $e) {
            Log::error('Échec reversement artisan FedaPay', ['artisan_id' => $artisan->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'FedaPay n’a pas pu lancer le reversement. Aucun paiement n’a été marqué comme versé.');
        }

        abort_unless(app(FedaPayService::class)->payoutSucceeded($payout), 422,
            'Le reversement est encore en traitement par FedaPay. Aucun paiement n’a été marqué comme versé.');

        $payoutId = $payout->id ?? null;
        $count = DB::transaction(function () use ($artisan, $request, $payoutId) {
            return Payment::whereHas('order', fn($q) => $q->where('artisan_id', $artisan->id))
                ->where('status', 'completed')
                ->whereNull('reversed_at')
                ->whereNull('refund_requested_at')
                ->lockForUpdate()
                ->update([
                    'reversed_at'   => now(),
                    'reversed_by'   => Auth::id(),
                    'reversal_note' => ($request->reversal_note ?? 'Reversement Mobile Money')
                        . ($payoutId ? " — FedaPay payout {$payoutId}" : ''),
                    'fedapay_payout_id' => $payoutId,
                ]);
        });

        AdminAuditLog::record('admin.wallet.reverse', $artisan, [
            'amount' => $amount,
            'count' => $count,
            'payout_id' => $payoutId,
            'note' => $request->reversal_note ?? 'Reversement Mobile Money',
        ]);

        return back()->with('success', "✅ {$count} paiement(s) marqué(s) comme reversé(s) à {$artisan->name}.");
    }

    public function confirmRefund(Payment $payment)
    {
        abort_if(!$payment->refundIsPending(), 422, 'Aucun remboursement en attente pour ce paiement.');

        $fedapay = app(FedaPayService::class);
        try {
            $payout = $payment->fedapay_payout_id
                ? $fedapay->retrievePayout($payment->fedapay_payout_id)
                : $fedapay->createRefundPayout(
                    (int) $payment->amount,
                    $payment->order->client,
                    "refund-payment-{$payment->id}"
                );
        } catch (\FedaPay\Error\ApiConnection|\FedaPay\Error\InvalidRequest $e) {
            $payment->update(['refund_status' => 'failed', 'refund_error' => $e->getMessage()]);
            Log::error('Échec remboursement FedaPay', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'FedaPay n’a pas pu lancer le remboursement. Vérifiez la configuration et réessayez.');
        }

        $payment->update([
            'fedapay_payout_id' => $payment->fedapay_payout_id ?: ($payout->id ?? null),
            'refund_status' => $payout->status ?? 'pending',
            'refund_error' => null,
        ]);

        if (!$fedapay->payoutSucceeded($payout)) {
            return back()->with('error', 'Le remboursement est encore en traitement par FedaPay. Vous pourrez vérifier son statut depuis cet écran.');
        }

        DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->refundIsPending(), 422, 'Aucun remboursement en attente pour ce paiement.');
            $locked->markRefunded(Auth::user());
        });

        AdminAuditLog::record('admin.wallet.confirm-refund', $payment, [
            'amount' => (int) $payment->amount,
            'payout_id' => $payment->fedapay_payout_id,
            'status' => 'confirmed',
        ]);

        try {
            $payment->order->client->notify(new \App\Notifications\RefundCompleted($payment->order, $payment));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification remboursement confirmé : ' . $e->getMessage());
        }

        return back()->with('success', "✅ Remboursement de {$payment->amount} XOF confirmé pour la commande #{$payment->order_id}.");
    }
}
