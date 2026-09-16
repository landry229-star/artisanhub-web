<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\ArtisanProfile;
use App\Services\NotificationService;
use App\Services\ContractService;
use App\Services\DeliveryService;
use App\Services\FedaPayService;
use App\Models\Service;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $artisan;

    protected function setUp(): void
    {
        parent::setUp();

        // Désactiver les notifications réelles dans tous les tests
        $this->mock(NotificationService::class)->shouldIgnoreMissing();

        $this->client = User::factory()->create([
            'role'              => 'client',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->artisan = User::factory()->create([
            'role'              => 'artisan',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        ArtisanProfile::factory()->create(['user_id' => $this->artisan->id]);
    }

    // ── Création de commande ──────────────────────────────────────────────────

    public function test_client_peut_passer_une_commande(): void
    {
        $response = $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id'  => $this->artisan->id,
            'title'       => 'Réparation robinet cuisine',
            'description' => 'Le robinet de la cuisine fuit depuis plusieurs jours, besoin urgent.',
            'budget'      => 25000,
            'deadline'    => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_PENDING,
        ]);
    }

    public function test_client_peut_joindre_jusqua_cinq_photos_privees(): void
    {
        Storage::fake('local');

        $files = array_map(
            fn ($name) => UploadedFile::fake()->image($name),
            ['un.jpg', 'deux.png', 'trois.webp']
        );

        $response = $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id' => $this->artisan->id,
            'title' => 'Fabrication d’une table',
            'description' => 'Je souhaite une table sur mesure avec ces inspirations visuelles.',
            'images' => $files,
        ]);

        $response->assertRedirect();
        $order = Order::latest('id')->first();
        $this->assertCount(3, $order->images);
        Storage::disk('local')->assertExists($order->images->first()->path);
    }

    public function test_plus_de_cinq_photos_sont_refusees(): void
    {
        $files = array_map(fn ($i) => UploadedFile::fake()->image("photo-{$i}.jpg"), range(1, 6));

        $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id' => $this->artisan->id,
            'title' => 'Fabrication d’une table',
            'description' => 'Je souhaite une table sur mesure avec ces inspirations visuelles.',
            'images' => $files,
        ])->assertSessionHasErrors('images');
    }

    public function test_photo_de_commande_est_protegee_pour_les_participants(): void
    {
        Storage::fake('local');
        $order = Order::factory()->create(['client_id' => $this->client->id, 'artisan_id' => $this->artisan->id]);
        $image = $order->images()->create(['path' => 'orders/1/images/photo.jpg']);
        Storage::disk('local')->put($image->path, 'image');

        $this->actingAs($this->client)->get(route('files.order-image', [$order, $image]))->assertOk();

        $other = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
        $this->actingAs($other)->get(route('files.order-image', [$order, $image]))->assertForbidden();
    }

    public function test_service_d_un_autre_artisan_ou_inactif_est_refuse(): void
    {
        $other = User::factory()->artisan()->create(['email_verified_at' => now(), 'is_active' => true]);
        $otherProfile = ArtisanProfile::factory()->create(['user_id' => $other->id]);
        $service = Service::create([
            'artisan_profile_id' => $otherProfile->id,
            'title' => 'Service concurrent',
            'description' => 'Description du service concurrent suffisamment longue.',
            'price' => 10000,
            'delay_days' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id' => $this->artisan->id,
            'service_id' => $service->id,
            'title' => 'Réparation robinet cuisine',
            'description' => 'Le robinet de la cuisine fuit depuis plusieurs jours, besoin urgent.',
        ]);

        $response->assertSessionHasErrors('service_id');
    }

    public function test_commande_avec_livraison_necessite_une_ville(): void
    {
        $response = $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id'     => $this->artisan->id,
            'title'          => 'Réparation robinet',
            'description'    => 'Le robinet de la cuisine fuit depuis plusieurs jours, besoin urgent.',
            'budget'         => 25000,
            'needs_delivery' => true,
            // delivery_city absent
        ]);

        $response->assertSessionHasErrors('delivery_city');
    }

    public function test_budget_inferieur_a_1000_est_refuse(): void
    {
        $response = $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id'  => $this->artisan->id,
            'title'       => 'Petit travail',
            'description' => 'Une description suffisamment longue pour passer la validation.',
            'budget'      => 500, // trop faible
        ]);

        $response->assertSessionHasErrors('budget');
    }

    public function test_titre_trop_court_est_refuse(): void
    {
        $response = $this->actingAs($this->client)->post(route('client.orders.store'), [
            'artisan_id'  => $this->artisan->id,
            'title'       => 'OK', // moins de 5 caractères
            'description' => 'Une description suffisamment longue pour passer la validation.',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_artisan_ne_peut_pas_passer_de_commande(): void
    {
        $response = $this->actingAs($this->artisan)->post(route('client.orders.store'), [
            'artisan_id'  => $this->artisan->id,
            'title'       => 'Travail pour moi',
            'description' => 'Une description suffisamment longue pour passer la validation.',
        ]);

        $response->assertForbidden();
    }

    // ── Flux de statut ────────────────────────────────────────────────────────

    public function test_artisan_peut_accepter_une_commande_en_attente(): void
    {
        $this->mock(ContractService::class)
             ->shouldReceive('generate')
             ->once()
             ->andReturn('contracts/contrat-test.html');

        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_PENDING,
        ]);
        Payment::factory()->completed()->create(['order_id' => $order->id]);

        $this->actingAs($this->artisan)
             ->patch(route('artisan.orders.accept', $order))
             ->assertRedirect();

        $this->assertEquals(Order::STATUS_ACCEPTED, $order->fresh()->status);
    }

    public function test_artisan_ne_peut_pas_accepter_une_commande_deja_acceptee(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_ACCEPTED,
        ]);

        $this->actingAs($this->artisan)
             ->patch(route('artisan.orders.accept', $order))
             ->assertStatus(422);
    }

    public function test_artisan_peut_demarrer_une_commande_acceptee(): void
    {
        $this->mock(DeliveryService::class)->shouldIgnoreMissing();

        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_ACCEPTED,
        ]);

        $this->actingAs($this->artisan)
             ->patch(route('artisan.orders.start', $order))
             ->assertRedirect();

        $this->assertEquals(Order::STATUS_IN_PROGRESS, $order->fresh()->status);
    }

    public function test_artisan_peut_marquer_une_commande_comme_livree(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($this->artisan)
             ->patch(route('artisan.orders.deliver', $order), [
                 'completion_photo' => UploadedFile::fake()->image('completion.jpg'),
             ])
             ->assertRedirect();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_client_peut_signaler_un_litige(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_DELIVERED,
        ]);

        $this->actingAs($this->client)
             ->patch(route('client.orders.dispute', $order))
             ->assertRedirect();

        $this->assertEquals(Order::STATUS_DISPUTED, $order->fresh()->status);
    }

    public function test_client_ne_peut_pas_signaler_litige_commande_en_cours(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($this->client)
             ->patch(route('client.orders.dispute', $order))
             ->assertStatus(422);
    }

    // ── Isolation des données ─────────────────────────────────────────────────

    public function test_client_ne_voit_pas_les_commandes_des_autres(): void
    {
        $autreClient = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);

        $order = Order::factory()->create([
            'client_id'  => $autreClient->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->client)
             ->get(route('client.orders.show', $order))
             ->assertForbidden();
    }

    public function test_artisan_ne_peut_pas_modifier_commande_dun_autre_artisan(): void
    {
        $autreArtisan = User::factory()->create(['role' => 'artisan', 'email_verified_at' => now()]);

        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $autreArtisan->id,
            'status'     => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->artisan)
             ->patch(route('artisan.orders.accept', $order))
             ->assertForbidden();
    }

    public function test_client_peut_annuler_une_commande_en_attente(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->client)
            ->patch(route('client.orders.cancel', $order), [
                'reason' => 'Le projet est reporté par le client.',
            ])
            ->assertRedirect(route('client.orders.index'));

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    // ── Transitions de statut (modèle Order) ─────────────────────────────────

    public function test_statuts_transitions_autorises(): void
    {
        $order = new Order(['status' => Order::STATUS_PENDING]);
        $this->assertTrue($order->canBeAccepted());
        $this->assertFalse($order->canBeStarted());
        $this->assertFalse($order->canBeCancelled() === false); // peut annuler en attente

        $order->status = Order::STATUS_ACCEPTED;
        $this->assertFalse($order->canBeAccepted());
        $this->assertTrue($order->canBeStarted());

        $order->status = Order::STATUS_IN_PROGRESS;
        $this->assertTrue($order->canBeDelivered());
        $this->assertFalse($order->canBeValidated());

        $order->status = Order::STATUS_DELIVERED;
        $this->assertTrue($order->canBeValidated());
        $this->assertTrue($order->canBeDisputed());
    }
}
