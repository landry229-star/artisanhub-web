<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Quote;
use App\Services\ContractService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Flux de devis négocié : avant qu'une commande soit acceptée, client et
 * artisan peuvent se faire des propositions de prix successives (rounds),
 * plutôt que d'être bloqués sur le budget indicatif saisi à la création.
 *
 * Une commande passe en "negotiation_status = agreed" dès qu'un devis est
 * accepté par l'une des deux parties ; le budget final de la commande est
 * alors celui du devis, et la commande passe directement au statut ACCEPTÉE
 * (contrat généré), sans étape supplémentaire.
 */
class QuoteController extends Controller
{
    public function __construct(
        private ContractService     $contractService,
        private NotificationService $notificationService,
    ) {}

    /** Propose un montant (première proposition ou contre-proposition). */
    public function store(Request $request, Order $order)
    {
        $role = $this->roleFor($order);
        abort_if(!$role, 403);
        abort_if(!$order->canProposeQuote(), 422, 'Cette commande n\'est plus ouverte à la négociation.');
        abort_if(!$order->canProposeMoreQuotes(), 422,
            'Nombre maximum de propositions atteint pour cette commande (' . Quote::MAX_ROUNDS . '). '
            . 'Acceptez la dernière offre ou annulez la commande.');

        $request->validate([
            'amount'  => ['required', 'integer', 'min:100'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        [$quote, $anomalyNote] = DB::transaction(function () use ($order, $request, $role) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$lockedOrder->canProposeQuote(), 422, 'Cette commande n\'est plus ouverte à la négociation.');
            abort_if($lockedOrder->quotes()->count() >= Quote::MAX_ROUNDS, 422,
                'Nombre maximum de propositions atteint pour cette commande.');

            $lockedOrder->quotes()->where('status', Quote::STATUS_PENDING)
                ->update(['status' => Quote::STATUS_COUNTERED]);
            $nextRound = (int) $lockedOrder->quotes()->max('round') + 1;
            $anomalyNote = $lockedOrder->priceAnomalyWarning((int) $request->amount);
            $quote = $lockedOrder->quotes()->create([
                'proposed_by_id'      => Auth::id(),
                'proposed_by_role'    => $role,
                'amount'              => $request->amount,
                'message'             => $request->message,
                'status'              => Quote::STATUS_PENDING,
                'round'               => $nextRound,
                'expires_at'          => now()->addHours(Quote::EXPIRY_HOURS),
                'price_anomaly_note'  => $anomalyNote,
            ]);
            $lockedOrder->update(['negotiation_status' => Order::NEGOTIATION_IN_PROGRESS]);
            $order->refresh();
            return [$quote, $anomalyNote];
        });

        if ($anomalyNote) {
            $this->notificationService->priceAnomalyDetected($order, $quote);
        }

        $this->notificationService->quoteProposed($order, $quote);

        $redirect = $role === 'artisan'
            ? route('artisan.orders.show', $order)
            : route('client.orders.show', $order);

        return redirect($redirect)->with('success', '💬 Proposition envoyée (' . $quote->formattedAmount() . ').');
    }

    /** Accepte le devis en attente : fixe le budget final et fait passer la commande à "acceptée". */
    public function accept(Order $order, Quote $quote)
    {
        $role = $this->roleFor($order);
        abort_if(!$role, 403);
        abort_if($quote->order_id !== $order->id, 404);
        abort_if(!$quote->isPending(), 422, 'Cette proposition n\'est plus valide.');
        abort_if($quote->isExpired(), 422, 'Cette proposition a expiré. Faites une nouvelle offre.');
        abort_if($quote->proposed_by_id === Auth::id(), 422, 'Vous ne pouvez pas accepter votre propre proposition.');

        $accepted = DB::transaction(function () use ($order, $quote) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedQuote = Quote::whereKey($quote->id)->where('order_id', $lockedOrder->id)
                ->lockForUpdate()->firstOrFail();
            abort_if($lockedOrder->status !== Order::STATUS_PENDING, 422,
                'Un devis ne peut être accepté que pour une commande en attente.');
            abort_if(!$lockedQuote->isPending(), 422, 'Cette proposition n\'est plus valide.');
            abort_if($lockedQuote->isExpired(), 422, 'Cette proposition a expiré.');
            abort_if($lockedQuote->proposed_by_id === Auth::id(), 422);

            $lockedQuote->update(['status' => Quote::STATUS_ACCEPTED]);
            $lockedOrder->update([
                'budget'             => $lockedQuote->amount,
                'negotiation_status' => Order::NEGOTIATION_AGREED,
                'status'             => Order::STATUS_ACCEPTED,
                'contract_path'      => $this->contractService->generate($lockedOrder),
            ]);
            $order->refresh();
            return true;
        });

        if ($accepted) {
            $this->notificationService->orderAccepted($order);
        }

        $redirect = $role === 'artisan'
            ? route('artisan.orders.show', $order)
            : route('client.orders.show', $order);

        return redirect($redirect)->with('success', '✅ Devis accepté à ' . $quote->formattedAmount() . ' ! Commande confirmée.');
    }

    /** Refuse la proposition en attente sans contre-proposer (l'autre partie peut relancer). */
    public function reject(Order $order, Quote $quote)
    {
        $role = $this->roleFor($order);
        abort_if(!$role, 403);
        abort_if($quote->order_id !== $order->id, 404);
        abort_if(!$quote->isPending(), 422);
        abort_if($quote->isExpired(), 422, 'Cette proposition a déjà expiré.');
        abort_if($quote->proposed_by_id === Auth::id(), 422);

        DB::transaction(function () use ($order, $quote) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedQuote = Quote::whereKey($quote->id)->where('order_id', $lockedOrder->id)
                ->lockForUpdate()->firstOrFail();
            abort_if(!$lockedQuote->isPending() || $lockedQuote->isExpired(), 422);
            abort_if($lockedQuote->proposed_by_id === Auth::id(), 422);
            $lockedQuote->update(['status' => Quote::STATUS_REJECTED]);
        });

        $redirect = $role === 'artisan'
            ? route('artisan.orders.show', $order)
            : route('client.orders.show', $order);

        return redirect($redirect)->with('warning', 'Proposition refusée. Vous pouvez faire une contre-proposition.');
    }

    private function roleFor(Order $order): ?string
    {
        if ($order->artisan_id === Auth::id()) return 'artisan';
        if ($order->client_id === Auth::id())   return 'client';
        return null;
    }
}
