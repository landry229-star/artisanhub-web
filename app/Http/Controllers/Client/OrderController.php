<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\FedaPayService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private FedaPayService      $fedaPay,
    ) {}

    public function index(Request $request)
    {
        $query = Order::where('client_id', Auth::id())
            ->with(['artisan.artisanProfile', 'review']);
            
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->latest()->paginate(10);
        return view('client.orders.index', compact('orders'));
    }

    public function create(int $artisan, Request $request)
    {
        $artisan = User::where('role', 'artisan')
            ->where('is_active', true)
            ->with(['artisanProfile.activeServices'])
            ->findOrFail($artisan);

        // Préremplir depuis un service si service_id passé en query string
        $service = null;
        $repeatOrder = null;
        if ($request->filled('service')) {
            $service = $artisan->artisanProfile?->activeServices?->firstWhere('id', $request->service);
        }
        if ($request->filled('repeat')) {
            $repeatOrder = Order::where('client_id', Auth::id())
                ->where('artisan_id', $artisan->id)
                ->with('service')
                ->find($request->integer('repeat'));
            if ($repeatOrder && $repeatOrder->service?->is_active) {
                $service ??= $repeatOrder->service;
            }
        }

        return view('client.orders.create', compact('artisan', 'service', 'repeatOrder'));
    }

    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $artisan = User::whereKey($request->artisan_id)->where('role', 'artisan')
                ->where('is_active', true)->lockForUpdate()->firstOrFail();
            $serviceId = $request->service_id ?: null;
            if ($serviceId && !$artisan->artisanProfile?->activeServices()->whereKey($serviceId)->exists()) {
                abort(422, 'Le service sélectionné n’est plus disponible.');
            }

            return Order::create([
            'client_id'      => Auth::id(),
            'artisan_id'     => $artisan->id,
            'service_id'     => $serviceId,
            'title'          => $request->title,
            'description'    => $request->description,
            'budget'         => $request->budget,
            'deadline'       => $request->deadline,
            'needs_delivery' => $request->boolean('needs_delivery'),
            'delivery_city'  => $request->boolean('needs_delivery') ? $request->delivery_city : null,
            'status'         => Order::STATUS_PENDING,
            ]);
        });

        foreach ($request->file('images', []) as $image) {
            $path = $image->store("orders/{$order->id}/images", 'local');
            $order->images()->create([
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
            ]);
        }

        $this->notificationService->orderPlaced($order);

        return redirect()->route('client.orders.show', $order)
            ->with('success', '✅ Commande envoyée ! L\'artisan va vous répondre sous 48h.');
    }

    public function show(Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        $order->load('artisan.artisanProfile', 'messages.sender', 'payment', 'review', 'guaranteeClaim', 'quotes', 'delivery.livreur', 'images');
        
        return view('client.orders.show', compact('order'));
    }

    /** Client valide la livraison → redirige vers FedaPay */
    public function validate(Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        abort_if(!$order->canBeValidated(), 422, 'Cette commande ne peut pas être validée maintenant.');

        // 1. Si pas de budget → terminer sans paiement
        if (!$order->budget || $order->budget <= 0) {
            DB::transaction(function () use ($order) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                abort_if(!$locked->canBeValidated(), 422);
                $locked->update(['status' => Order::STATUS_COMPLETED]);
                $order->refresh();
            });
            $this->notificationService->orderCompleted($order);
            
            return redirect()->route('client.orders.show', $order)
                ->with('success', '🎉 Commande validée et terminée !');
        }

        // 2. Avec budget → passer par FedaPay
        try {
            // Le service reçoit l'objet $order complet pour extraire ses données
            $paymentUrl = $this->fedaPay->createTransaction($order);
            
            return redirect()->away($paymentUrl);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur FedaPay [Commande {$order->id}] : " . $e->getMessage());
            
            return back()->with('error', 'Erreur lors de l\'initialisation du paiement. Veuillez réessayer.');
        }
    }

    /** Le client signe électroniquement la remise avant la clôture de livraison. */
    public function signDelivery(Request $request, Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        $delivery = $order->delivery()->firstOrFail();
        abort_unless($delivery->status === 'recuperee', 422, 'La livraison ne peut pas encore être signée.');

        $request->validate([
            'signature' => ['required', 'string', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+$/'],
        ]);

        $encoded = substr($request->string('signature')->toString(), strlen('data:image/png;base64,'));
        $binary = base64_decode($encoded, true);
        abort_if($binary === false || strlen($binary) > 1_000_000, 422, 'La signature est invalide.');

        $path = "deliveries/{$delivery->id}/signature-" . now()->format('YmdHis') . '.png';
        Storage::disk('local')->put($path, $binary);

        $delivery->update([
            'proof_signature_path' => $path,
            'proof_verified_at' => now(),
            'proof_method' => 'signature',
        ]);

        return back()->with('success', '✅ Signature de remise enregistrée.');
    }

    /** Client annule une commande avant démarrage */
    public function cancel(Request $request, Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        abort_if(!$order->canBeCancelled(), 422, 'Cette commande ne peut pas être annulée maintenant.');
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        DB::transaction(function () use ($order, $request) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeCancelled(), 422);
            $locked->update([
                'status' => Order::STATUS_CANCELLED,
                'cancellation_reason' => $request->string('reason')->trim()->toString(),
            ]);
            $order->refresh();
        });
        $this->notificationService->orderCancelled($order);

        return redirect()->route('client.orders.index')
            ->with('success', '✅ Commande annulée. L\'artisan a été informé.');
    }

    /** Client signale un litige */
    public function dispute(Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        abort_if(!$order->canBeDisputed(), 422);

        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeDisputed(), 422);
            $locked->update(['status' => Order::STATUS_DISPUTED]);
            $order->refresh();
        });
        $this->notificationService->orderDisputed($order);

        return back()->with("warning", "⚠️ Litige signalé. Notre équipe intervient sous 48h.");
    }
}