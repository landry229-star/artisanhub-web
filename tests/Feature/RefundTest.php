<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\FedaPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_start_and_confirm_a_fedapay_payout_refund(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create([
            'phone' => '97000000',
            'phone_verified_at' => now(),
        ]);
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'amount' => 25_000,
            'refund_requested_at' => now(),
            'refund_requested_by' => $admin->id,
            'refund_status' => 'requested',
        ]);

        $payout = (object) ['id' => 'PAYOUT-123', 'status' => 'sent'];
        $fedapay = $this->mock(FedaPayService::class);
        $fedapay->shouldReceive('createRefundPayout')
            ->once()
            ->with(25_000, \Mockery::on(fn (User $user) => $user->is($client)), "refund-payment-{$payment->id}")
            ->andReturn($payout);
        $fedapay->shouldReceive('payoutSucceeded')->once()->with($payout)->andReturnTrue();

        $this->actingAs($admin)
            ->patch(route('admin.wallet.confirm-refund', $payment))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
            'fedapay_payout_id' => 'PAYOUT-123',
            'refund_status' => 'sent',
        ]);
    }

    public function test_refund_requires_a_verified_client_phone(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create(['phone' => '97000000']);
        $order = Order::factory()->create(['client_id' => $client->id]);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'refund_requested_at' => now(),
            'refund_requested_by' => $admin->id,
        ]);

        $this->mock(FedaPayService::class)
            ->shouldReceive('createRefundPayout')
            ->once()
            ->andThrow(new \Symfony\Component\HttpKernel\Exception\HttpException(
                422,
                'Le numéro du client doit être vérifié avant un remboursement.'
            ));

        $this->actingAs($admin)
            ->patch(route('admin.wallet.confirm-refund', $payment))
            ->assertStatus(422);
    }
}
