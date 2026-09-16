<?php

namespace Tests\Feature;

use App\Models\ArtisanProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Mail;
use App\Notifications\OrderAccepted;
use App\Notifications\OrderPlaced;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSpaceTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $artisan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = User::factory()->client()->create([
            'email_verified_at' => now(),
            'phone' => '97000000',
            'phone_verified_at' => now(),
        ]);
        $this->artisan = User::factory()->artisan()->create(['email_verified_at' => now()]);
        ArtisanProfile::factory()->create(['user_id' => $this->artisan->id]);
    }

    public function test_client_phone_change_invalidates_phone_verification(): void
    {
        $this->actingAs($this->client)
            ->put(route('client.profile.update'), [
                'name' => $this->client->name,
                'phone' => '96000000',
                'city' => $this->client->city,
            ])
            ->assertRedirect();

        $this->assertNull($this->client->fresh()->phone_verified_at);
    }

    public function test_client_can_view_own_payments_and_download_documents(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($this->client)
            ->get(route('client.wallet.index'))
            ->assertOk()
            ->assertSee($order->title);

        $this->actingAs($this->client)
            ->get(route('client.wallet.statement'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->client)
            ->get(route('client.wallet.receipt', $payment))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_client_cannot_view_another_clients_payment_receipt(): void
    {
        $other = User::factory()->client()->create(['email_verified_at' => now()]);
        $order = Order::factory()->create([
            'client_id' => $other->id,
            'artisan_id' => $this->artisan->id,
        ]);
        $payment = Payment::factory()->completed()->create(['order_id' => $order->id]);

        $this->actingAs($this->client)
            ->get(route('client.wallet.receipt', $payment))
            ->assertForbidden();
    }

    public function test_client_can_update_and_delete_delivery_address(): void
    {
        $this->actingAs($this->client)
            ->put(route('client.profile.update'), [
                'name' => $this->client->name,
                'phone' => $this->client->phone,
                'city' => $this->client->city,
                'quartier' => 'Zongo',
                'delivery_address' => 'Maison bleue près du marché',
                'latitude' => 6.3703,
                'longitude' => 2.3912,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->client->id,
            'quartier' => 'Zongo',
            'delivery_address' => 'Maison bleue près du marché',
        ]);

        $this->actingAs($this->client)
            ->delete(route('client.profile.delivery-address.clear'))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->client->id,
            'delivery_address' => null,
            'quartier' => null,
        ]);
    }

    public function test_client_notification_preferences_control_order_channels(): void
    {
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);
        $this->client->update([
            'order_notifications' => false,
            'email_notifications' => false,
            'whatsapp_opt_in' => false,
        ]);

        $this->assertSame([], (new OrderAccepted($order))->via($this->client));

        $this->client->update(['order_notifications' => true, 'email_notifications' => false]);
        $this->assertSame(['database'], (new OrderPlaced($order))->via($this->client));
    }

    public function test_client_can_repeat_an_existing_service_order(): void
    {
        $service = Service::create([
            'artisan_profile_id' => $this->artisan->artisanProfile->id,
            'title' => 'Réparation plomberie',
            'description' => 'Description du service',
            'price' => 15000,
            'delay_days' => 3,
            'is_active' => true,
        ]);
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'service_id' => $service->id,
            'title' => 'Ancienne demande',
            'description' => 'Ancienne description',
            'budget' => 17000,
            'deadline' => now()->addDays(4),
        ]);

        $this->actingAs($this->client)
            ->get(route('client.orders.create', [$this->artisan->id, 'repeat' => $order->id]))
            ->assertOk()
            ->assertSee('Ancienne demande')
            ->assertSee('Ancienne description')
            ->assertSee('17000');
    }

    public function test_support_submission_creates_a_trackable_ticket(): void
    {
        Mail::fake();

        $this->actingAs($this->client)
            ->post(route('support.send'), [
                'name' => $this->client->name,
                'email' => $this->client->email,
                'subject' => 'remboursement',
                'message' => 'Je souhaite connaître le statut de mon remboursement.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->client->id,
            'status' => 'open',
        ]);
        $this->assertSame(1, SupportTicket::where('user_id', $this->client->id)->count());
        $this->actingAs($this->client)
            ->get(route('client.support.index'))
            ->assertOk()
            ->assertSee('AH-');
    }

    public function test_admin_can_assign_and_reply_to_a_support_ticket(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $ticket = SupportTicket::create([
            'number' => 'AH-2026-TEST01',
            'user_id' => $this->client->id,
            'email' => $this->client->email,
            'subject' => 'Commande',
            'message' => 'Je n’ai pas reçu la confirmation de ma commande.',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.support.index'))
            ->assertOk()
            ->assertSee($ticket->number);

        $this->actingAs($admin)
            ->patch(route('admin.support.assign', $ticket), [
                'assigned_to' => $admin->id,
                'priority' => 'high',
            ])->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.support.reply', $ticket), [
                'message' => 'Nous vérifions votre commande et vous répondons dans les plus brefs délais.',
            ])->assertRedirect();

        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'body' => 'Nous vérifions votre commande et vous répondons dans les plus brefs délais.',
        ]);
    }
}
