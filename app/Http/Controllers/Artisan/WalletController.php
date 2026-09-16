<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Paiements complétés
        $payments = Payment::whereHas('order', fn($q) => $q->where('artisan_id', $user->id))
            ->where('status', 'completed')
            ->with('order.client')
            ->latest('paid_at')
            ->limit(100)
            ->get();

        $paymentQuery = Payment::whereHas('order', fn ($q) => $q->where('artisan_id', $user->id))
            ->where('status', 'completed');
        $totalEarned = (clone $paymentQuery)->sum('net_amount');
        $monthEarned = (clone $paymentQuery)->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)->sum('net_amount');
        $totalOrders = (clone $paymentQuery)->count();

        // Commandes en cours (argent à recevoir)
        $pendingOrders = Order::where('artisan_id', $user->id)
            ->whereIn('status', ['livree', 'en_cours', 'acceptee'])
            ->with('client', 'payment')
            ->latest()
            ->limit(100)
            ->get();
        $pendingBudget = Order::where('artisan_id', $user->id)
            ->whereIn('status', ['livree', 'en_cours', 'acceptee'])->sum('budget');

        $commissionRate = Payment::getCommissionRate($user->id);

        $stats = [
            'total_earned'   => $totalEarned,
            'total_month'    => $monthEarned,
            // Bug corrigé : on affichait la somme des budgets bruts (avant
            // commission), ce qui annonçait à l'artisan un montant "à
            // recevoir" plus élevé que ce qu'il touchera réellement. On
            // applique désormais le même taux de commission que celui
            // utilisé pour l'estimation affichée par commande.
            'pending_amount' => round($pendingBudget * (1 - $commissionRate)),
            'total_orders'   => $totalOrders,
            'commission_rate'=> $commissionRate,
        ];

        // Évolution 6 derniers mois
        $monthly = collect(range(5, 0))->map(function ($ago) use ($user) {
            $date = now()->subMonths($ago);
            $earned = Payment::whereHas('order', fn($q) => $q->where('artisan_id', $user->id))
                ->where('status', 'completed')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('net_amount');
            return ['label' => $date->format('M Y'), 'amount' => (int) $earned];
        });

        return view('artisan.wallet.index', compact('stats', 'payments', 'pendingOrders', 'monthly'));
    }

    public function statement()
    {
        $artisan = Auth::user();
        $payments = Payment::whereHas('order', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->whereIn('status', ['completed', 'refunded'])
            ->with('order.client')
            ->latest('paid_at')
            ->get();

        return Pdf::loadView('artisan.wallet.statement', compact('artisan', 'payments'))
            ->setPaper('a4', 'portrait')
            ->download('releve-financier-artisanhub-' . now()->format('Y-m') . '.pdf');
    }

    public function receipt(Payment $payment)
    {
        $artisan = Auth::user();
        abort_unless($payment->order()->where('artisan_id', $artisan->id)->exists(), 403);
        abort_unless(in_array($payment->status, ['completed', 'refunded'], true), 404);

        return Pdf::loadView('artisan.wallet.receipt', compact('artisan', 'payment'))
            ->setPaper('a4', 'portrait')
            ->download('justificatif-paiement-' . $payment->id . '.pdf');
    }
}
