<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Delivery;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $deliveryService) {}

    public function index(Request $request)
    {
        $query = Delivery::with(['order.client', 'order.artisan', 'livreur']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('city')) {
            $query->where('delivery_city', $request->city);
        }

        $deliveries = $query->latest()->paginate(20);

        $stats = [
            'en_recherche' => Delivery::where('status','en_recherche')->count(),
            'active'       => Delivery::whereIn('status',['assignee','acceptee','en_route','recuperee'])->count(),
            'livrees'      => Delivery::where('status','livree')->count(),
            'echouees'     => Delivery::where('status','echouee')->count(),
        ];

        return view('admin.deliveries.index', compact('deliveries','stats'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load('order.client','order.artisan','livreur');
        return view('admin.deliveries.show', compact('delivery'));
    }

    /** Admin réassigne manuellement un livreur */
    public function reassign(Request $request, Delivery $delivery)
    {
        $request->validate([
            'livreur_id' => ['required', 'exists:users,id'],
        ]);

        $livreur = User::where('role','livreur')->where('is_active', true)
            ->where('is_livreur_available', true)->findOrFail($request->livreur_id);

        DB::transaction(function () use ($delivery, $livreur) {
            $locked = Delivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['en_recherche', 'assignee', 'echouee'], true), 422);
            $locked->update(['livreur_id' => $livreur->id, 'status' => 'assignee']);
            $delivery->refresh();
        });

        try {
            $livreur->notify(new \App\Notifications\DeliveryAssigned($delivery));
        } catch (\Exception $e) {}

        AdminAuditLog::record('admin.delivery.reassign', $delivery, [
            'old_livreur_id' => $delivery->livreur_id,
            'new_livreur_id' => $livreur->id,
            'new_livreur_name' => $livreur->name,
            'order_id' => $delivery->order_id,
        ]);

        return back()->with('success', "Livraison réassignée à {$livreur->name}.");
    }
}
