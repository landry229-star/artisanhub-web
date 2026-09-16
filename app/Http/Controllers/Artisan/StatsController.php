<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $start = now()->subMonths(11)->startOfMonth();
        $monthlyPayments = Payment::whereHas('order', fn($q)=>$q->where('artisan_id',$user->id))
            ->where('status','completed')->where('paid_at', '>=', $start)
            ->selectRaw('YEAR(paid_at) as payment_year, MONTH(paid_at) as payment_month, SUM(net_amount) as earned')
            ->groupBy('payment_year', 'payment_month')->get()
            ->keyBy(fn ($row) => $row->payment_year . '-' . $row->payment_month);
        $monthlyOrders = Order::where('artisan_id',$user->id)->where('status','terminee')
            ->where('updated_at', '>=', $start)
            ->selectRaw('YEAR(updated_at) as order_year, MONTH(updated_at) as order_month, COUNT(*) as total')
            ->groupBy('order_year', 'order_month')->get()
            ->keyBy(fn ($row) => $row->order_year . '-' . $row->order_month);

        $monthly = collect(range(11,0))->map(function($ago) use ($monthlyPayments, $monthlyOrders) {
            $date = now()->subMonths($ago);
            $key = $date->year . '-' . $date->month;
            return [
                'label'  => $date->format('M Y'),
                'earned' => (int) ($monthlyPayments[$key]->earned ?? 0),
                'orders' => (int) ($monthlyOrders[$key]->total ?? 0),
            ];
        });

        $total    = Order::where('artisan_id',$user->id)->count();
        $accepted = Order::where('artisan_id',$user->id)->whereIn('status',['acceptee','en_cours','livree','terminee'])->count();

        $stats = [
            'total_earned'    => Payment::whereHas('order',fn($q)=>$q->where('artisan_id',$user->id))->where('status','completed')->sum('net_amount'),
            'month_earned'    => Payment::whereHas('order',fn($q)=>$q->where('artisan_id',$user->id))->where('status','completed')->whereMonth('paid_at',now()->month)->whereYear('paid_at',now()->year)->sum('net_amount'),
            'total_orders'    => $total,
            'completed_orders'=> Order::where('artisan_id',$user->id)->where('status','terminee')->count(),
            'accept_rate'     => $total>0 ? round(($accepted/$total)*100) : 0,
            'avg_rating'      => $user->artisanProfile?->rating ?? 0,
            'reviews_count'   => $user->artisanProfile?->reviews_count ?? 0,
        ];

        $ratingCounts = Review::where('artisan_id', $user->id)
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')->pluck('total', 'rating');
        $ratingDist = collect([5,4,3,2,1])->mapWithKeys(fn($r) => [$r => (int) ($ratingCounts[$r] ?? 0)]);

        return view('artisan.stats.index', compact('stats','monthly','ratingDist'));
    }
}
