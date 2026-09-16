<?php

namespace Tests\Feature;

use App\Models\ArtisanProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderDelayed;
use App\Services\NotificationService;
use App\Services\FedaPayService;
use Tests\TestCase;

class ArtisanSpaceTest extends TestCase
{
    use RefreshDatabase;

    private User $artisan;
    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan = User::factory()->create([
            'role' => 'artisan', 'email_verified_at' => now(), 'phone' => '97000000',
        ]);
        ArtisanProfile::factory()->create(['user_id' => $this->artisan->id]);
        $this->client = User::factory()->client()->create();
    }

    public function test_artisan_cannot_accept_an_unpaid_order(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id, 'artisan_id' => $this->artisan->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->artisan)
            ->patch(route('artisan.orders.accept', $order))
            ->assertStatus(422);
    }

    public function test_artisan_can_accept_a_paid_order(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id, 'artisan_id' => $this->artisan->id,
            'status' => Order::STATUS_PENDING,
        ]);
        Payment::factory()->completed()->create(['order_id' => $order->id]);
        $this->mock(ContractService::class)->shouldReceive('generate')->once()->andReturn('contracts/test.html');

        $this->actingAs($this->artisan)
            ->patch(route('artisan.orders.accept', $order))
            ->assertRedirect();

        $this->assertSame(Order::STATUS_ACCEPTED, $order->fresh()->status);
    }

    public function test_changing_phone_invalidates_phone_verification(): void
    {
        $this->artisan->update(['phone_verified_at' => now()]);

        $this->actingAs($this->artisan)
            ->put(route('artisan.profile.update'), [
                'name' => $this->artisan->name, 'phone' => '96000000',
                'city' => $this->artisan->city, 'specialty' => 'Menuisier',
                'category' => 'autre',
            ])->assertRedirect();

        $this->assertNull($this->artisan->fresh()->phone_verified_at);
    }

    public function test_artisan_can_edit_only_its_own_service(): void
    {
        $service = Service::create([
            'artisan_profile_id' => $this->artisan->artisanProfile->id,
            'title' => 'Ancien service', 'description' => 'Description',
            'price' => 10000, 'delay_days' => 3, 'is_active' => true,
        ]);

        $this->actingAs($this->artisan)
            ->patch(route('artisan.services.update', $service), [
                'title' => 'Service modifié', 'description' => 'Description mise à jour',
                'price' => 12000, 'delay_days' => 5,
            ])->assertRedirect(route('artisan.services.index'));

        $this->assertDatabaseHas('services', ['id' => $service->id, 'title' => 'Service modifié', 'price' => 12000]);
    }

    public function test_artisan_cannot_edit_another_artisan_service(): void
    {
        $other = User::factory()->create(['role' => 'artisan', 'email_verified_at' => now()]);
        $profile = ArtisanProfile::factory()->create(['user_id' => $other->id]);
        $service = Service::create([
            'artisan_profile_id' => $profile->id,
            'title' => 'Service', 'description' => 'Description',
            'price' => 10000, 'delay_days' => 3, 'is_active' => true,
        ]);

        $this->actingAs($this->artisan)
            ->patch(route('artisan.services.update', $service), [
                'title' => 'Intrusion', 'description' => 'x', 'price' => 1000, 'delay_days' => 1,
            ])->assertForbidden();
    }

    public function test_artisan_portfolio_upload_is_private_to_owner_actions(): void
    {
        Storage::fake('public');
        $this->actingAs($this->artisan)
            ->post(route('artisan.portfolio.store'), [
                'title' => 'Référence', 'image' => UploadedFile::fake()->image('reference.jpg'),
            ])->assertRedirect();

        $this->assertDatabaseHas('portfolio_items', ['title' => 'Référence']);
    }

    public function test_new_order_notification_targets_artisan_mail_and_database_channels(): void
    {
        Notification::fake();
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);

        app(NotificationService::class)->orderPlaced($order);

        Notification::assertSentTo($this->artisan, OrderPlaced::class);
        $notification = new OrderPlaced($order);
        $this->assertContains('mail', $notification->via($this->artisan));
        $this->assertContains('database', $notification->via($this->artisan));
    }

    public function test_admin_reversement_uses_fedapay_payout_before_marking_payments_paid(): void
    {
        $admin = User::factory()->admin()->create();
        $this->artisan->update(['phone_verified_at' => now()]);
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'net_amount' => 15000,
        ]);
        $payout = (object) ['id' => 'PAYOUT-ARTISAN', 'status' => 'sent'];

        $fedapay = $this->mock(FedaPayService::class);
        $fedapay->shouldReceive('createMobileMoneyPayout')
            ->once()
            ->with(15000, \Mockery::on(fn (User $user) => $user->is($this->artisan)), \Mockery::type('string'))
            ->andReturn($payout);
        $fedapay->shouldReceive('payoutSucceeded')->once()->with($payout)->andReturnTrue();

        $this->actingAs($admin)
            ->patch(route('admin.wallet.reverse', $this->artisan))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($payment->fresh()->reversed_at);
        $this->assertSame('PAYOUT-ARTISAN', $payment->fresh()->fedapay_payout_id);
    }

    public function test_artisan_refus_requires_and_stores_reason(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id, 'artisan_id' => $this->artisan->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->artisan)
            ->patch(route('artisan.orders.reject', $order), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->actingAs($this->artisan)
            ->patch(route('artisan.orders.reject', $order), ['reason' => 'Planning indisponible cette semaine'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CANCELLED,
            'rejection_reason' => 'Planning indisponible cette semaine',
        ]);
    }

    public function test_client_cancellation_requires_and_stores_reason(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id, 'artisan_id' => $this->artisan->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->client)
            ->patch(route('client.orders.cancel', $order), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->actingAs($this->client)
            ->patch(route('client.orders.cancel', $order), ['reason' => 'Projet reporté par le client'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CANCELLED,
            'cancellation_reason' => 'Projet reporté par le client',
        ]);
    }

    public function test_delayed_order_is_alerted_once(): void
    {
        Notification::fake();
        $order = Order::factory()->create([
            'client_id' => $this->client->id, 'artisan_id' => $this->artisan->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'deadline' => now()->subDay(),
        ]);

        $this->artisan('orders:alert-delayed')->assertExitCode(0);
        Notification::assertSentTo([$this->client, $this->artisan], OrderDelayed::class);
        $this->assertNotNull($order->fresh()->delay_alerted_at);

        Notification::fake();
        $this->artisan('orders:alert-delayed')->assertExitCode(0);
        Notification::assertNothingSent();
    }

    public function test_kyc_document_accepts_multiple_user_profiles(): void
    {
        Storage::fake('local');

        foreach ([$this->artisan, $this->client] as $user) {
            $this->actingAs($user)
                ->post(route('kyc.document.upload'), [
                    'id_document_type' => 'cni',
                    'id_document' => UploadedFile::fake()->create('piece.pdf', 100, 'application/pdf'),
                ])->assertRedirect();

            $this->assertSame('pending', $user->fresh()->id_document_status);
            Storage::disk('local')->assertExists($user->fresh()->id_document_path);
        }
    }

    public function test_artisan_can_download_financial_statement_and_payment_receipt(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($this->artisan)
            ->get(route('artisan.wallet.statement'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->artisan)
            ->get(route('artisan.wallet.receipt', $payment))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_artisan_cannot_download_another_artisan_payment_receipt(): void
    {
        $other = User::factory()->artisan()->create(['email_verified_at' => now()]);
        ArtisanProfile::factory()->create(['user_id' => $other->id]);
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $other->id,
        ]);
        $payment = Payment::factory()->completed()->create(['order_id' => $order->id]);

        $this->actingAs($this->artisan)
            ->get(route('artisan.wallet.receipt', $payment))
            ->assertForbidden();
    }
}
