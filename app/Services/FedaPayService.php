<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FedaPayService
{
    public function __construct()
    {
        \FedaPay\FedaPay::setApiKey(config('services.fedapay.secret_key'));
        \FedaPay\FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));
    }

    public function createTransaction(Order $order): string
    {
        if (!$order->budget || $order->budget <= 0) {
            throw new \Exception("Budget invalide pour la commande #{$order->id}");
        }

        [$payment, $amounts] = DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if(!$lockedOrder->canBeValidated(), 422, 'Cette commande ne peut pas être payée.');
            \App\Models\User::whereKey($lockedOrder->artisan_id)->lockForUpdate()->first();
            $amounts = Payment::calculateAmounts($lockedOrder->budget, $lockedOrder->artisan_id);
            $existing = Payment::where('order_id', $lockedOrder->id)->lockForUpdate()->first();
            if ($existing && ($existing->isCompleted() || $existing->isPending())) {
                throw new \RuntimeException('Un paiement est déjà en cours ou confirmé pour cette commande.');
            }

            $attributes = [
                'amount'               => $amounts['amount'],
                'commission_rate'      => $amounts['commission_rate'],
                'commission'           => $amounts['commission'],
                'guarantee_contribution' => $amounts['guarantee_contribution'],
                'net_amount'           => $amounts['net_amount'],
                'method'               => 'mtn_mobile_money',
                'status'               => 'pending',
                'fedapay_transaction_id' => null,
            ];
            if ($existing) {
                $existing->update($attributes);
                return [$existing->refresh(), $amounts];
            }
            return [Payment::create(['order_id' => $lockedOrder->id] + $attributes), $amounts];
        });

        try {
            $transaction = \FedaPay\Transaction::create([
                'description'  => "Commande #{$order->id} — " . substr($order->title, 0, 50),
                'amount'       => (int) $amounts['amount'],
                'currency'     => ['iso' => 'XOF'],
                'callback_url' => route('payment.callback'),
                'return_url'   => route('payment.success') . '?order=' . $order->id,
                'cancel_url'   => route('payment.failure') . '?order=' . $order->id,
                'customer'     => [
                    'firstname' => $this->getFirstname($order->client->name),
                    'lastname'  => $this->getLastname($order->client->name),
                    'email'     => $order->client->email,
                ],
            ]);

            $token = $transaction->generateToken();

            // Sauvegarder l'ID de transaction
            Payment::whereKey($payment->id)->whereNull('fedapay_transaction_id')
                ->where('status', 'pending')
                ->update(['fedapay_transaction_id' => $transaction->id]);

            Log::info("FedaPay transaction créée", [
                'order_id'       => $order->id,
                'transaction_id' => $transaction->id,
                'amount'         => $amounts['amount'],
                'commission_rate'=> $amounts['commission_rate'],
            ]);

            return $token->url;

        } catch (\FedaPay\Error\ApiConnection $e) {
            $payment->update(['status' => 'failed']);
            Log::error("FedaPay connexion échouée [Commande {$order->id}] : " . $e->getMessage());
            throw new \Exception("Service de paiement indisponible. Réessayez dans quelques minutes.");

        } catch (\FedaPay\Error\InvalidRequest $e) {
            $payment->update(['status' => 'failed']);
            Log::error("FedaPay requête invalide [Commande {$order->id}] : " . $e->getMessage());
            throw new \Exception("Erreur de configuration du paiement. Contactez le support.");

        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            Log::error("FedaPay erreur [Commande {$order->id}] : " . $e->getMessage());
            throw new \Exception("Erreur lors du paiement : " . $e->getMessage());
        }
    }

    /**
     * Crée puis démarre un dépôt Mobile Money destiné à rembourser un client.
     * FedaPay expose ce flux comme un payout, pas comme une annulation de
     * transaction. L'appel reste donc explicitement validé par un admin.
     */
    public function createRefundPayout(int $amount, \App\Models\User $customer, string $reference): object
    {
        return $this->createMobileMoneyPayout($amount, $customer, $reference);
    }

    public function createMobileMoneyPayout(int $amount, \App\Models\User $customer, string $reference): object
    {
        abort_if($amount < 1, 422, 'Le montant du remboursement est invalide.');
        abort_if(blank($customer->phone), 422, 'Le client doit avoir un numéro de téléphone pour être remboursé.');
        abort_if(!$customer->isPhoneVerified(), 422, 'Le numéro du bénéficiaire doit être vérifié avant un virement.');

        $phone = preg_replace('/\D+/', '', (string) $customer->phone);
        if (str_starts_with($phone, '229')) {
            $phone = substr($phone, 3);
        }
        abort_if(strlen($phone) < 8, 422, 'Le numéro du client est invalide pour un remboursement Mobile Money.');

        $payout = \FedaPay\Payout::create([
            'amount' => $amount,
            'currency' => ['iso' => 'XOF'],
            'mode' => 'mobile_money',
            'customer' => [
                'firstname' => $this->getFirstname($customer->name),
                'lastname' => $this->getLastname($customer->name),
                'email' => $customer->email,
                'phone_number' => ['number' => $phone, 'country' => 'bj'],
            ],
            'merchant_reference' => $reference,
        ]);

        $started = $payout->sendNow(['phone_number' => $phone]);
        return $started->payouts[0] ?? $started;
    }

    public function retrievePayout(string $payoutId): object
    {
        return \FedaPay\Payout::retrieve($payoutId);
    }

    public function payoutSucceeded(object $payout): bool
    {
        return in_array($payout->status ?? null, ['sent', 'transferred', 'completed'], true);
    }

    public function handleWebhook(Request $request): void
    {
        $signature = $request->header('X-FEDAPAY-SIGNATURE', '');
        $payload   = $request->getContent();

        $event = \FedaPay\Webhook::constructEvent(
            $payload,
            $signature,
            config('services.fedapay.webhook_secret')
        );

        Log::info("FedaPay webhook : {$event->name}");

        match ($event->name) {
            'transaction.approved' => $this->onApproved($event->transaction),
            'transaction.declined' => $this->onDeclined($event->transaction),
            'transaction.canceled' => $this->onCanceled($event->transaction),
            default => null,
        };
    }

    private function onApproved(object $transaction): void
    {
        $result = DB::transaction(function () use ($transaction) {
            $payment = Payment::where('fedapay_transaction_id', $transaction->id)
                ->lockForUpdate()->firstOrFail();
            if ($payment->isCompleted()) {
                return null;
            }
            abort_if($payment->status !== 'pending', 422, 'Paiement non validable.');
            $payment->update(['status' => 'completed', 'paid_at' => now()]);
            $order = $payment->order()->lockForUpdate()->firstOrFail();
            abort_if($order->status !== Order::STATUS_DELIVERED, 422, 'Commande non livrée.');
            $order->update(['status' => Order::STATUS_COMPLETED]);
            return $order;
        });

        if (!$result) {
            Log::info("Webhook ignoré (déjà traité) — transaction {$transaction->id}");
            return;
        }

        $order = $result->load('artisan.artisanProfile');
        $order->artisan->artisanProfile?->recalculateTier();
        app(NotificationService::class)->orderCompleted($order);
        Log::info("Paiement validé — commande #{$order->id}");
    }

    private function onDeclined(object $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $payment = Payment::where('fedapay_transaction_id', $transaction->id)->lockForUpdate()->first();
            if ($payment && $payment->isPending()) {
                $payment->update(['status' => 'failed']);
            }
        });
        Log::warning("Paiement refusé — transaction {$transaction->id}");
    }

    private function onCanceled(object $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $payment = Payment::where('fedapay_transaction_id', $transaction->id)->lockForUpdate()->first();
            if ($payment && $payment->isPending()) {
                $payment->update(['status' => 'failed']);
            }
        });
        Log::info("Paiement annulé — transaction {$transaction->id}");
    }

    private function getFirstname(string $name): string
    {
        return explode(' ', trim($name))[0] ?? $name;
    }

    private function getLastname(string $name): string
    {
        $parts = explode(' ', trim($name), 2);
        return $parts[1] ?? '';
    }
}
