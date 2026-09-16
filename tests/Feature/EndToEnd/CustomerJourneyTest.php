<?php

namespace Tests\Feature\EndToEnd;

use App\Models\ArtisanProfile;
use App\Models\Order;
use App\Models\User;
use App\Services\FedaPayService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_journey_from_registration_to_order(): void
    {
        Mail::fake();
        $this->mock(NotificationService::class)->shouldIgnoreMissing();

        $artisan = User::factory()->artisan()->create();
        ArtisanProfile::factory()->create(['user_id' => $artisan->id]);

        $registration = $this->post('/inscription', [
            'name' => 'Client E2E',
            'email' => 'client-e2e@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role' => 'client',
            'phone' => '0022967000099',
            'city' => 'Cotonou',
        ]);

        $registration->assertRedirect(route('email.notice'));
        $client = User::where('email', 'client-e2e@example.com')->firstOrFail();
        $client->markEmailAsVerified();

        $response = $this->actingAs($client)->post(route('client.orders.store'), [
            'artisan_id' => $artisan->id,
            'title' => 'Réparation plomberie',
            'description' => 'Réparation urgente du robinet de la cuisine.',
            'budget' => 25000,
            'deadline' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'artisan_id' => $artisan->id,
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function test_fedapay_callback_is_received_without_user_session(): void
    {
        $this->mock(FedaPayService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->withArgs(fn ($request) => $request->input('event') === 'transaction.approved');

        $this->postJson(route('payment.callback'), [
            'event' => 'transaction.approved',
        ])->assertOk();
    }
}
