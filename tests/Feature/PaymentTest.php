<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\ArtisanProfile;
use App\Services\FedaPayService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $artisan;

    protected function setUp(): void
    {
        parent::setUp();

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

    // ── Calcul des commissions ────────────────────────────────────────────────

    public function test_commission_tier1_pour_artisan_debutant(): void
    {
        // Artisan sans paiement ce mois → taux 5%
        $result = Payment::calculateAmounts(20_000, $this->artisan->id);

        $this->assertEquals(0.05,   $result['commission_rate']);
        $this->assertEquals(1_000,  $result['commission']);
        $this->assertEquals(19_000, $result['net_amount']);
        $this->assertEquals(20_000, $result['amount']);
    }

    public function test_commission_tier2_apres_50000_xof_ce_mois(): void
    {
        // Simuler 60 000 XOF déjà encaissés ce mois
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);

        Payment::factory()->create([
            'order_id'   => $order->id,
            'amount'     => 60_000,
            'status'     => 'completed',
            'paid_at'    => now(),
        ]);

        $rate = Payment::getCommissionRate($this->artisan->id);
        $this->assertEquals(0.08, $rate); // tier 2 : 8%
    }

    public function test_commission_tier3_au_dela_de_200000_xof(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount'   => 250_000,
            'status'   => 'completed',
            'paid_at'  => now(),
        ]);

        $rate = Payment::getCommissionRate($this->artisan->id);
        $this->assertEquals(0.10, $rate); // tier 3 : 10%
    }

    public function test_commission_mois_precedent_ne_compte_pas(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
        ]);

        // Paiement du mois dernier → ne doit pas affecter le calcul
        Payment::factory()->create([
            'order_id' => $order->id,
            'amount'   => 300_000,
            'status'   => 'completed',
            'paid_at'  => now()->subMonth(),
        ]);

        $rate = Payment::getCommissionRate($this->artisan->id);
        $this->assertEquals(0.05, $rate); // repart à zéro ce mois
    }

    public function test_calcul_sans_artisan_utilise_taux_minimum(): void
    {
        $result = Payment::calculateAmounts(10_000, null);
        $this->assertEquals(0.05, $result['commission_rate']);
    }

    // ── Flux de paiement ─────────────────────────────────────────────────────

    public function test_validation_commande_sans_budget_termine_sans_paiement(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_DELIVERED,
            'budget'     => null,
        ]);

        $this->actingAs($this->client)
             ->patch(route('client.orders.validate', $order))
             ->assertRedirect(route('client.orders.show', $order));

        $this->assertEquals(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_validation_commande_avec_budget_redirige_vers_fedapay(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_DELIVERED,
            'budget'     => 25_000,
        ]);

        // Mock FedaPay pour ne pas appeler l'API réelle
        $this->mock(FedaPayService::class)
             ->shouldReceive('createTransaction')
             ->once()
             ->with(\Mockery::on(fn($o) => $o->id === $order->id))
             ->andReturn('https://sandbox.fedapay.com/pay/test-token');

        $response = $this->actingAs($this->client)
                         ->patch(route('client.orders.validate', $order));

        $response->assertRedirect('https://sandbox.fedapay.com/pay/test-token');
    }

    public function test_echec_fedapay_affiche_message_erreur(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_DELIVERED,
            'budget'     => 25_000,
        ]);

        $this->mock(FedaPayService::class)
             ->shouldReceive('createTransaction')
             ->once()
             ->andThrow(new \Exception('API timeout'));

        $response = $this->actingAs($this->client)
                         ->patch(route('client.orders.validate', $order));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        // Commande ne doit pas être marquée comme terminée
        $this->assertNotEquals(Order::STATUS_COMPLETED, $order->fresh()->status);
    }

    public function test_client_ne_peut_pas_valider_une_commande_en_attente(): void
    {
        $order = Order::factory()->create([
            'client_id'  => $this->client->id,
            'artisan_id' => $this->artisan->id,
            'status'     => Order::STATUS_PENDING,
        ]);

        $this->actingAs($this->client)
             ->patch(route('client.orders.validate', $order))
             ->assertStatus(422);
    }

    // ── Labels de commission ──────────────────────────────────────────────────

    public function test_label_commission_tier1(): void
    {
        $payment = new Payment(['commission_rate' => 0.05]);
        $this->assertEquals('5% (Débutant)', $payment->commissionLabel());
    }

    public function test_label_commission_tier2(): void
    {
        $payment = new Payment(['commission_rate' => 0.08]);
        $this->assertEquals('8% (Actif)', $payment->commissionLabel());
    }

    public function test_label_commission_tier3(): void
    {
        $payment = new Payment(['commission_rate' => 0.10]);
        $this->assertEquals('10% (Top)', $payment->commissionLabel());
    }
}
