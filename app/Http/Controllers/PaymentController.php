<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\FedaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private FedaPayService $fedaPay,
    ) {}

    /**
     * Initie un paiement FedaPay pour une commande.
     * NOTE : FedaPayService::createTransaction() attend l'Order complet
     * (il calcule lui-même les montants, la commission et sauvegarde le
     * Payment en base AVANT de contacter FedaPay). Il retourne directement
     * l'URL de paiement (string), pas un objet transaction.
     */
    public function initiate(Order $order)
    {
        abort_if($order->client_id !== Auth::id(), 403);
        abort_if(!$order->canBeValidated(), 422);

        try {
            $paymentUrl = $this->fedaPay->createTransaction($order);

            return redirect($paymentUrl);

        } catch (\Exception $e) {
            Log::error("Paiement — initiation échouée [Commande #{$order->id}] : " . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Webhook FedaPay. La vérification de signature + le traitement
     * (approved/declined/canceled) sont entièrement délégués à
     * FedaPayService::handleWebhook(), qui est la seule source de vérité
     * sur le format des événements FedaPay.
     */
    public function callback(Request $request)
    {
        try {
            $this->fedaPay->handleWebhook($request);

        } catch (\Exception $e) {
            Log::error('FedaPay webhook error: ' . $e->getMessage());
            return response('Webhook error', 400);
        }

        return response('OK', 200);
    }

    public function success(Request $request)
    {
        $order = Order::with('payment')->findOrFail($request->integer('order'));

        abort_if($order->client_id !== Auth::id(), 403);
        abort_if(!$order->payment?->isCompleted(), 422, 'Le paiement n\'est pas encore confirmé.');

        return redirect()->route('client.orders.show', $order)
            ->with('success', 'Paiement confirmé !');
    }

    public function failure(Request $request)
    {
        $order = Order::findOrFail($request->integer('order'));
        abort_if($order->client_id !== Auth::id(), 403);

        return redirect()->route('client.orders.show', $order)
            ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
    }
}
