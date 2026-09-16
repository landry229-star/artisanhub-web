<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminExportLog;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = Order::with(['client', 'artisan']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where('title', 'like', "%{$request->search}%");

        $orders = $query->latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function arbitrate(Request $request, Order $order)
{
    // 1. Correction : Utilisation des constantes pour la validation pour éviter les erreurs de frappe
    // Assurez-vous que ces constantes existent dans votre modèle Order
    $request->validate([
        'decision'   => ['required', 'in:terminee,annulee,remboursee'],
        'admin_note' => ['required', 'string', 'min:10'],
    ]);

    // Autoriser l'arbitrage si la commande est en "litige" OU "terminée"
    $allowedStatuses = [Order::STATUS_DISPUTED, Order::STATUS_COMPLETED];

    abort_if(!in_array($order->status, $allowedStatuses), 422, 'Cette commande ne peut pas être arbitrée (elle n\'est ni en litige, ni terminée).');
    // 3. Logique de mise à jour
    // On détermine le statut final en fonction de la décision
    $newStatus = match ($request->decision) {
        'remboursee' => Order::STATUS_CANCELLED, // Si remboursée, on annule la commande
        'terminee'   => Order::STATUS_COMPLETED, // Assurez-vous que cette constante existe
        'annulee'    => Order::STATUS_CANCELLED,
        default      => $order->status,
    };

    // Si décision "remboursée" : on ne transfère rien ici (pas d'API de
    // remboursement FedaPay fiable — voir Payment::markRefundRequested).
    // On trace juste la demande ; un admin doit ensuite la confirmer une
    // fois le remboursement lancé depuis le flux FedaPay administrateur.
    $payment = DB::transaction(function () use ($order, $request, $newStatus) {
        $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
        abort_if(!in_array($lockedOrder->status, [Order::STATUS_DISPUTED, Order::STATUS_COMPLETED]), 422,
            'Cette commande ne peut pas être arbitrée.');
        $payment = $lockedOrder->payment()->lockForUpdate()->first();
        if ($request->decision === 'remboursee') {
            abort_if(!$payment || !$payment->isCompleted(), 422,
                'Aucun paiement complété à rembourser pour cette commande.');
            $payment->markRefundRequested(auth()->user(), $request->admin_note);
        }
        $lockedOrder->update(['status' => $newStatus, 'admin_note' => $request->admin_note]);
        $order->refresh();
        return $payment;
    });

    if ($request->decision === 'remboursee' && $payment) {
        $this->notificationService->refundRequested($order, $payment);
    }

    // Le palier de confiance dépend du taux de litige (commandes en litige /
    // (terminées + litiges)). Un arbitrage fait sortir la commande du statut
    // "litige" (vers terminée ou annulée), donc le taux change immédiatement —
    // sans ce recalcul, le palier restait figé jusqu'à la prochaine commande
    // ou le prochain avis de cet artisan.
    $order->artisan->artisanProfile?->recalculateTier();

    // 4. Notification
    // Il est conseillé de vérifier si le service existe pour éviter des erreurs inattendues
    if (isset($this->notificationService)) {
        $this->notificationService->orderArbitrated($order);
    }

    return back()->with('success', 'Litige arbitré. Les deux parties ont été notifiées.');
}

    public function export(Request $request)
    {
        $query = Order::with(['client', 'artisan'])->latest('id');
        $rows = $query->get();
        $format = $request->string('format')->value() ?: 'csv';
        $filters = $request->except('format');

        if ($format === 'pdf') {
            $filename = 'commandes-' . now()->format('Ymd-His') . '.pdf';
            AdminExportLog::create([
                'user_id' => Auth::id(),
                'resource' => 'commandes',
                'format' => 'pdf',
                'filename' => $filename,
                'filters' => $filters,
            ]);

            return Pdf::loadView('admin.exports.orders', compact('rows'))->setPaper('a4', 'landscape')->download($filename);
        }
        if ($format === 'excel') {
            $filename = 'commandes-' . now()->format('Ymd-His') . '.xls';
            AdminExportLog::create([
                'user_id' => Auth::id(),
                'resource' => 'commandes',
                'format' => 'excel',
                'filename' => $filename,
                'filters' => $filters,
            ]);

            return response()->view('admin.exports.orders-excel', compact('rows'), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        }

        $filename = 'commandes-' . now()->format('Ymd-His') . '.csv';
        AdminExportLog::create([
            'user_id' => Auth::id(),
            'resource' => 'commandes',
            'format' => 'csv',
            'filename' => $filename,
            'filters' => $filters,
        ]);

        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename=' . $filename];

        return response()->stream(function () use ($query) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");
            fputcsv($f, ['ID','Titre','Client','Artisan','Budget','Statut','Date']);
            $query->chunkById(500, function ($orders) use ($f) {
                foreach ($orders as $o) {
                    fputcsv($f, [
                        $o->id, $o->title, $o->client?->name, $o->artisan?->name,
                        $o->budget, $o->statusLabel(), $o->created_at->format('d/m/Y'),
                    ]);
                }
            }, 'id');
            fclose($f);
        }, 200, $headers);
    }
}
