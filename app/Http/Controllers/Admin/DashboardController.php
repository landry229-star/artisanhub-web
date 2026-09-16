<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Order, Payment, Delivery, Service, AdminExportLog};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_artisans'  => User::where('role', 'artisan')->count(),
            'total_clients'   => User::where('role', 'client')->count(),
            'pending_orders'  => Order::where('status', 'en_attente')->count(),
            'disputed_orders' => Order::where('status', 'litige')->count(),
            'completed_orders'=> Order::where('status', 'terminee')->count(),
            'total_revenue'   => Payment::where('status', 'completed')->sum('commission'),
            'unverified'      => User::where('role', 'artisan')->where('is_verified', false)->count(),
        ];

        $recentOrders = Order::with(['client', 'artisan'])
            ->whereIn('status', ['litige', 'en_attente'])
            ->latest()->take(10)->get();

        $recentUsers = User::latest()->take(5)->get();
        $recentExports = AdminExportLog::with('user')->latest()->take(5)->get();

        $advancedStats = $this->buildAdvancedStats();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentUsers', 'recentExports', 'advancedStats'));
    }

    /**
     * Statistiques avancées consommées par la section "Statistiques avancées"
     * du dashboard (KPIs secondaires + graphiques Chart.js).
     */
    private function buildAdvancedStats(): array
    {
        // ── Panier moyen : moyenne des paiements complétés ──────────────
        $avgBasket = (float) Payment::where('status', 'completed')->avg('amount');

        // ── Taux de conversion : commandes terminées / total commandes ──
        $totalOrders     = Order::count();
        $completedOrders = Order::where('status', 'terminee')->count();
        $conversionRate  = $totalOrders > 0
            ? round(($completedOrders / $totalOrders) * 100)
            : 0;

        // ── Nouveaux inscrits sur les 7 derniers jours ───────────────────
        $since7Days = Carbon::now()->subDays(7);
        $newClientsWeek  = User::where('role', 'client')->where('created_at', '>=', $since7Days)->count();
        $newArtisansWeek = User::where('role', 'artisan')->where('created_at', '>=', $since7Days)->count();

        // ── Ville la plus active (le plus d'utilisateurs) ────────────────
        $topCityRow = User::select('city', DB::raw('count(*) as total'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('total')
            ->first();
        $topCity = $topCityRow->city ?? '—';

        // ── Spécialité la plus demandée (via les profils artisans) ──────
        $topSpecialtyRow = DB::table('artisan_profiles')
            ->select('specialty', DB::raw('count(*) as total'))
            ->whereNotNull('specialty')
            ->where('specialty', '!=', '')
            ->groupBy('specialty')
            ->orderByDesc('total')
            ->first();
        $topSpecialty = $topSpecialtyRow->specialty ?? '—';

        // ── Évolution des revenus (commissions) sur les 30 derniers jours ─
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::now()->subDays($i)->startOfDay());

        $paymentsByDay = Payment::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(commission) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenueLabels = $days->map(fn ($d) => $d->format('d/m'))->values()->all();
        $revenueValues = $days->map(fn ($d) => (float) ($paymentsByDay[$d->toDateString()] ?? 0))->values()->all();

        // ── Revenus par ville / artisan / service ────────────────────────────
        $revenueByCity = Order::join('users as clients', 'clients.id', '=', 'orders.client_id')
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw('clients.city as city, COUNT(orders.id) as orders_count, COALESCE(SUM(payments.amount), 0) as revenue')
            ->whereNotNull('clients.city')
            ->where('clients.city', '!=', '')
            ->groupBy('clients.city')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $revenueByArtisan = Order::join('users as artisans', 'artisans.id', '=', 'orders.artisan_id')
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw('artisans.name as artisan, COUNT(orders.id) as orders_count, COALESCE(SUM(payments.amount), 0) as revenue')
            ->groupBy('artisans.id', 'artisans.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $revenueByService = Order::join('services', 'services.id', '=', 'orders.service_id')
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw('services.title as service, COUNT(orders.id) as orders_count, COALESCE(SUM(payments.amount), 0) as revenue')
            ->whereNotNull('services.title')
            ->groupBy('services.id', 'services.title')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ── Taux d’acceptation et litiges / remboursements ────────────────
        $totalOrders = Order::count();
        $acceptedOrders = Order::whereIn('status', [Order::STATUS_ACCEPTED, Order::STATUS_IN_PROGRESS, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])->count();
        $acceptanceRate = $totalOrders > 0 ? round(($acceptedOrders / $totalOrders) * 100) : 0;

        $disputedOrders = Order::where('status', Order::STATUS_DISPUTED)->count();
        $resolvedOrders = Order::whereIn('status', [Order::STATUS_CANCELLED, Order::STATUS_COMPLETED])->count();
        $disputeRate = $resolvedOrders > 0 ? round(($disputedOrders / ($resolvedOrders + $disputedOrders)) * 100) : 0;

        $refundsCount = Payment::whereIn('refund_status', ['requested', 'pending', 'completed', 'failed'])->count();
        $completedPayments = Payment::where('status', 'completed')->count();
        $refundRate = $completedPayments > 0 ? round(($refundsCount / $completedPayments) * 100) : 0;

        // ── Temps moyen de livraison ────────────────────────────────────────
        $deliveryDurations = Delivery::whereNotNull('accepted_at')
            ->whereNotNull('delivered_at')
            ->get()
            ->map(fn(Delivery $delivery) => $delivery->accepted_at && $delivery->delivered_at
                ? $delivery->accepted_at->diffInMinutes($delivery->delivered_at)
                : null)
            ->filter(fn($minutes) => !is_null($minutes));

        $avgDeliveryMinutes = $deliveryDurations->isNotEmpty() ? (int) round($deliveryDurations->avg()) : 0;

        // ── Répartition des commandes par statut ──────────────────────────
        $statusMap = [
            'en_attente' => 'En attente',
            'acceptee'   => 'Acceptée',
            'en_cours'   => 'En cours',
            'livree'     => 'Livrée',
            'terminee'   => 'Terminée',
            'annulee'    => 'Annulée',
            'litige'     => 'Litige',
        ];

        $countsByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusLabels = [];
        $statusValues = [];
        foreach ($statusMap as $key => $label) {
            $count = (int) ($countsByStatus[$key] ?? 0);
            if ($count > 0) {
                $statusLabels[] = $label;
                $statusValues[] = $count;
            }
        }
        // Sécurité : si aucune commande, on évite un graphique vide cassé
        if (empty($statusLabels)) {
            $statusLabels = ['Aucune donnée'];
            $statusValues = [0];
        }

        return [
            'avg_basket'        => round($avgBasket),
            'conversion_rate'   => $conversionRate,
            'new_clients_week'  => $newClientsWeek,
            'new_artisans_week' => $newArtisansWeek,
            'top_city'          => $topCity,
            'top_specialty'     => $topSpecialty,
            'revenue_labels'    => $revenueLabels,
            'revenue_values'    => $revenueValues,
            'status_labels'     => $statusLabels,
            'status_values'     => $statusValues,
            'revenue_by_city'   => $revenueByCity,
            'revenue_by_artisan'=> $revenueByArtisan,
            'revenue_by_service'=> $revenueByService,
            'acceptance_rate'   => $acceptanceRate,
            'dispute_rate'      => $disputeRate,
            'refund_rate'       => $refundRate,
            'avg_delivery_minutes' => $avgDeliveryMinutes,
        ];
    }
}
