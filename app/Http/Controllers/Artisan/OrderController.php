<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ContractService;
use App\Services\DeliveryService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private ContractService     $contractService,
        private NotificationService $notificationService,
        private DeliveryService     $deliveryService,
        private \App\Services\ImageService $imageService,
    ) {}

    public function index(Request $request)
    {
        $query = Order::where('artisan_id', Auth::id())
            ->with(['client']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $orders = $query->latest()->paginate(10);
        return view('artisan.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_if($order->artisan_id !== Auth::id(), 403);
        $order->load('client', 'messages.sender', 'payment', 'review', 'delivery.livreur', 'quotes', 'images');
        return view('artisan.orders.show', compact('order'));
    }

    /** ① Accepter → génère le contrat */
    public function accept(Order $order)
    {
        abort_if($order->artisan_id !== Auth::id(), 403);
        abort_if(!$order->canBeAccepted(), 422);
        abort_if(!$order->payment || !$order->payment->isCompleted(), 422,
            'La commande ne peut être acceptée qu’après confirmation du paiement.');

        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeAccepted(), 422);
            $payment = $locked->payment()->lockForUpdate()->first();
            abort_if(!$payment || !$payment->isCompleted(), 422,
                'La commande ne peut être acceptée qu’après confirmation du paiement.');
            $contractPath = $this->contractService->generate($locked);
            $locked->update([
                'status'        => Order::STATUS_ACCEPTED,
                'contract_path' => $contractPath,
            ]);
            $order->refresh();
        });
        $this->notificationService->orderAccepted($order);

        return back()->with('success', '✅ Commande acceptée ! Contrat envoyé au client.');
    }

    /** ② Démarrer → Bug 6 corrigé : déclenche la livraison si needs_delivery */
    public function start(Order $order)
    {
        abort_if($order->artisan_id !== Auth::id(), 403);
        abort_if(!$order->canBeStarted(), 422);

        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeStarted(), 422);
            $locked->update(['status' => Order::STATUS_IN_PROGRESS]);
            $order->refresh();
        });
        $this->notificationService->orderStarted($order);

        // Déclencher la recherche de livreur si nécessaire
        $deliveryMsg = '';
        if ($order->needs_delivery) {
            $delivery = $this->deliveryService->assignForOrder($order);
            $deliveryMsg = $delivery
                ? ' Un livreur est en cours de recherche.'
                : ' Aucun livreur disponible pour le moment.';
        }

        return back()->with('success', '🔨 Travail démarré ! Client notifié.' . $deliveryMsg);
    }

    /** ③ Livrer → preuve de réalisation obligatoire + notifie le client */
    public function markDelivered(Request $request, Order $order)
    {
        abort_if($order->artisan_id !== Auth::id(), 403);
        abort_if(!$order->canBeDelivered(), 422);

        // Preuve de réalisation obligatoire : protège le client (preuve en
        // cas de litige) et alimente automatiquement le portfolio artisan.
        $request->validate([
            'completion_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'completion_photo.required' => 'Une photo du travail terminé est obligatoire pour livrer la commande.',
        ]);

        $photoPath = $this->imageService->store(
            $request->file('completion_photo'),
            'completions',
            1600,
            1600,
            78,
            'local'
        );

        DB::transaction(function () use ($order, $photoPath) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeDelivered(), 422);
            $locked->update([
                'status'                => Order::STATUS_DELIVERED,
                'completion_photo_path' => $photoPath,
            ]);
            $order->refresh();
        });

        $this->notificationService->orderDelivered($order);

        return back()->with('success', '📦 Commande marquée comme livrée ! Client notifié.');
    }

    /** Annuler */
    public function cancel(Request $request, Order $order)
    {
        abort_if($order->artisan_id !== Auth::id(), 403);
        abort_if(!$order->canBeCancelled(), 422);
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeCancelled(), 422);
            $locked->update([
                'status' => Order::STATUS_CANCELLED,
                'cancellation_reason' => $request->string('reason')->trim()->toString(),
            ]);
            $order->refresh();
        });
        $this->notificationService->orderCancelled($order);

        return back()->with('success', 'Commande annulée.');
    }

    /** Refuser une nouvelle commande avec un motif explicite. */
    public function reject(Request $request, Order $order)
    {
        abort_if($order->artisan_id !== Auth::id(), 403);
        abort_if(!$order->canBeAccepted(), 422);
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        DB::transaction(function () use ($order, $request) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$locked->canBeAccepted(), 422);
            $locked->update([
                'status' => Order::STATUS_CANCELLED,
                'rejection_reason' => $request->string('reason')->trim()->toString(),
            ]);
            $order->refresh();
        });
        $this->notificationService->orderCancelled($order);

        return back()->with('success', 'Commande refusée. Le client a été informé.');
    }
}
