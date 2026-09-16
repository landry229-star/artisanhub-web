<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $payments = Payment::whereHas('order', fn ($q) => $q->where('client_id', Auth::id()))
            ->with('order.artisan')
            ->whereIn('status', ['completed', 'refunded'])
            ->latest('paid_at')
            ->paginate(20);

        $totalSpent = Payment::whereHas('order', fn ($q) => $q->where('client_id', Auth::id()))
            ->where('status', 'completed')
            ->sum('amount');

        return view('client.wallet.index', compact('payments', 'totalSpent'));
    }

    public function statement()
    {
        $client = Auth::user();
        $payments = Payment::whereHas('order', fn ($q) => $q->where('client_id', $client->id))
            ->with('order.artisan')
            ->whereIn('status', ['completed', 'refunded'])
            ->latest('paid_at')
            ->get();

        return Pdf::loadView('client.wallet.statement', compact('client', 'payments'))
            ->setPaper('a4', 'portrait')
            ->download('releve-paiements-artisanhub-' . now()->format('Y-m') . '.pdf');
    }

    public function receipt(Payment $payment)
    {
        $client = Auth::user();
        abort_unless($payment->order()->where('client_id', $client->id)->exists(), 403);
        abort_unless(in_array($payment->status, ['completed', 'refunded'], true), 404);

        return Pdf::loadView('client.wallet.receipt', compact('client', 'payment'))
            ->setPaper('a4', 'portrait')
            ->download('facture-artisanhub-' . $payment->id . '.pdf');
    }
}
