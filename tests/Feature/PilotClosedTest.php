<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PilotClosedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_operational_spaces_but_client_cannot(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($client)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_livreur_can_progress_a_delivery_and_cannot_access_another_mission(): void
    {
        Notification::fake();
        $livreur = User::factory()->livreur()->create();
        $client = User::factory()->client()->create();
        $artisan = User::factory()->artisan()->create();

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'artisan_id' => $artisan->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'livreur_id' => $livreur->id,
            'status' => 'assignee',
            'pickup_city' => 'Cotonou',
            'delivery_city' => 'Porto-Novo',
        ]);
        $otherDelivery = Delivery::create([
            'order_id' => Order::factory()->create()->id,
            'livreur_id' => User::factory()->livreur()->create()->id,
            'status' => 'assignee',
            'pickup_city' => 'Cotonou',
            'delivery_city' => 'Cotonou',
        ]);

        $this->actingAs($livreur)
            ->patch(route('livreur.missions.accept', $delivery))
            ->assertRedirect();

        $this->assertSame('acceptee', $delivery->fresh()->status);

        $this->actingAs($livreur)
            ->get(route('livreur.missions.show', $otherDelivery))
            ->assertForbidden();
    }

    public function test_delivery_requires_client_proof_code_before_completion(): void
    {
        $livreur = User::factory()->livreur()->create();
        $client = User::factory()->client()->create();
        $artisan = User::factory()->artisan()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'artisan_id' => $artisan->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'livreur_id' => $livreur->id,
            'status' => 'recuperee',
            'pickup_city' => 'Cotonou',
            'delivery_city' => 'Cotonou',
            'proof_code_hash' => Hash::make('123456'),
            'proof_code' => '123456',
        ]);

        $this->actingAs($livreur)
            ->patch(route('livreur.missions.deliver', $delivery), ['proof_code' => '000000'])
            ->assertStatus(422);

        $this->actingAs($livreur)
            ->patch(route('livreur.missions.deliver', $delivery), ['proof_code' => '123456'])
            ->assertRedirect();

        $this->assertTrue($delivery->fresh()->hasDeliveryProof());
        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_client_can_sign_delivery_before_livreur_completes_it(): void
    {
        Storage::fake('local');
        $client = User::factory()->client()->create();
        $artisan = User::factory()->artisan()->create();
        $livreur = User::factory()->livreur()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'artisan_id' => $artisan->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'livreur_id' => $livreur->id,
            'status' => 'recuperee',
            'pickup_city' => 'Cotonou',
            'delivery_city' => 'Cotonou',
        ]);
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

        $this->actingAs($client)
            ->post(route('client.orders.sign-delivery', $order), [
                'signature' => 'data:image/png;base64,'.$png,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $delivery->refresh();
        $this->assertSame('signature', $delivery->proof_method);
        $this->assertTrue($delivery->hasDeliveryProof());
        Storage::disk('local')->assertExists($delivery->proof_signature_path);

        $this->actingAs($livreur)
            ->patch(route('livreur.missions.deliver', $delivery))
            ->assertRedirect();

        $this->assertSame('livree', $delivery->fresh()->status);
    }

    public function test_user_can_anonymize_and_disable_own_account(): void
    {
        $user = User::factory()->client()->create(['password' => 'secret-password']);

        $this->actingAs($user)
            ->delete(route('account.destroy'), ['password' => 'secret-password'])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Compte supprimé',
            'is_active' => 0,
        ]);
    }

    public function test_support_sends_internal_and_customer_confirmation_emails(): void
    {
        $this->post(route('support.send'), [
            'name' => 'Client pilote',
            'email' => 'client@example.com',
            'subject' => 'commande',
            'message' => 'Ma commande pilote nécessite une vérification du support.',
        ])->assertRedirect()->assertSessionHas('success');

        $support = View::make('emails.support', [
            'senderName' => 'Client pilote',
            'senderEmail' => 'client@example.com',
            'subject' => 'Problème de commande',
            'userMessage' => 'Ma commande pilote nécessite une vérification du support.',
            'userId' => null,
        ])->render();
        $confirmation = View::make('emails.support_confirmation', [
            'name' => 'Client pilote',
            'subject' => 'Problème de commande',
        ])->render();

        $this->assertStringContainsString('Nouveau message support', $support);
        $this->assertStringContainsString('Votre demande a bien été reçue', $confirmation);
    }

    public function test_kyc_document_is_private_and_otp_can_be_verified(): void
    {
        Storage::fake('local');
        $user = User::factory()->client()->create(['phone' => '97000000']);
        $otp = null;
        $this->mock(WhatsAppSmsService::class)
            ->shouldReceive('send')
            ->once()
            ->andReturnUsing(function (string $phone, string $message) use ($user, &$otp): bool {
                $user->refresh();
                preg_match('/(\d{6})/', $message, $matches);
                $otp = $matches[1];
                return true;
            });

        $this->actingAs($user)
            ->post(route('kyc.document.upload'), [
                'id_document_type' => 'cni',
                'id_document' => UploadedFile::fake()->create('cni.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('pending', $user->id_document_status);
        $this->assertStringStartsWith('kyc/', $user->id_document_path);

        $this->actingAs($user)
            ->post(route('kyc.phone.send'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('kyc.phone.verify'), ['code' => $otp])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_client_can_review_completed_order_and_artisan_can_reply(): void
    {
        $client = User::factory()->client()->create();
        $artisan = User::factory()->artisan()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'artisan_id' => $artisan->id,
            'status' => Order::STATUS_COMPLETED,
        ]);

        $this->actingAs($client)
            ->post(route('reviews.store'), [
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Très bonne prestation pendant le pilote.',
            ])
            ->assertRedirect();

        $review = Review::firstOrFail();
        $this->assertSame(5, $review->rating);

        $this->actingAs($artisan)
            ->patch(route('reviews.reply', $review), [
                'reply' => 'Merci pour votre retour.',
            ])
            ->assertRedirect();

        $this->assertSame('Merci pour votre retour.', $review->fresh()->artisan_reply);
    }

    public function test_livreur_can_view_its_earning_summary(): void
    {
        $livreur = User::factory()->livreur()->create();
        $client = User::factory()->client()->create();
        $artisan = User::factory()->artisan()->create();

        $completed = Delivery::create([
            'order_id' => Order::factory()->create([
                'client_id' => $client->id,
                'artisan_id' => $artisan->id,
                'status' => Order::STATUS_DELIVERED,
                'title' => 'Commande 1',
            ])->id,
            'livreur_id' => $livreur->id,
            'status' => 'livree',
            'pickup_city' => 'Cotonou',
            'delivery_city' => 'Abomey-Calavi',
            'fee' => 7500,
            'delivered_at' => now(),
        ]);

        Delivery::create([
            'order_id' => Order::factory()->create([
                'client_id' => $client->id,
                'artisan_id' => $artisan->id,
                'status' => Order::STATUS_IN_PROGRESS,
                'title' => 'Commande 2',
            ])->id,
            'livreur_id' => $livreur->id,
            'status' => 'acceptee',
            'pickup_city' => 'Cotonou',
            'delivery_city' => 'Parakou',
            'fee' => 15000,
        ]);

        $this->actingAs($livreur)
            ->get(route('livreur.wallet.index'))
            ->assertOk()
            ->assertSee('Mon solde')
            ->assertSee('7 500 XOF')
            ->assertSee('15 000 XOF');

        $this->assertTrue($completed->fresh()->status === 'livree');
    }

    public function test_health_endpoint_is_available_for_monitoring(): void
    {
        $this->get('/up')->assertOk();
    }
}
