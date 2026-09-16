<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role'              => $role,
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);
    }

    // ── Accès aux dashboards par rôle ────────────────────────────────────────

    public function test_client_accede_uniquement_a_son_dashboard(): void
    {
        $client = $this->makeUser('client');

        $this->actingAs($client)->get(route('client.dashboard'))->assertOk();
        $this->actingAs($client)->get(route('artisan.dashboard'))->assertForbidden();
        $this->actingAs($client)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($client)->get(route('livreur.dashboard'))->assertForbidden();
    }

    public function test_artisan_accede_uniquement_a_son_dashboard(): void
    {
        $artisan = $this->makeUser('artisan');

        $this->actingAs($artisan)->get(route('artisan.dashboard'))->assertOk();
        $this->actingAs($artisan)->get(route('client.dashboard'))->assertForbidden();
        $this->actingAs($artisan)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($artisan)->get(route('livreur.dashboard'))->assertForbidden();
    }

    public function test_admin_accede_uniquement_a_son_dashboard(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('client.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('artisan.dashboard'))->assertForbidden();
    }

    public function test_livreur_accede_uniquement_a_son_dashboard(): void
    {
        $livreur = $this->makeUser('livreur');

        $this->actingAs($livreur)->get(route('livreur.dashboard'))->assertOk();
        $this->actingAs($livreur)->get(route('client.dashboard'))->assertForbidden();
        $this->actingAs($livreur)->get(route('artisan.dashboard'))->assertForbidden();
        $this->actingAs($livreur)->get(route('admin.dashboard'))->assertForbidden();
    }

    // ── Protection des routes authentifiées ──────────────────────────────────

    #[DataProvider('routesProtegees')]
    public function test_routes_proteges_redirigent_invite(string $method, string $url): void
    {
        $this->call($method, $url)->assertRedirect(route('login'));
    }

    public static function routesProtegees(): array
    {
        return [
            ['GET',  '/client/commandes'],
            ['GET',  '/artisan/commandes'],
            ['GET',  '/admin/utilisateurs'],
            ['GET',  '/livreur/missions'],
            ['GET',  '/notifications/api'],
        ];
    }

    // ── Rate limiting login ───────────────────────────────────────────────────

    public function test_rate_limiter_bloque_apres_5_tentatives(): void
    {
        $user = User::factory()->create(['password' => bcrypt('bonmotdepasse')]);

        // 5 tentatives échouées
        for ($i = 0; $i < 5; $i++) {
            $this->post('/connexion', [
                'email'    => $user->email,
                'password' => 'mauvais',
            ]);
        }

        // La 6e doit être bloquée par le rate limiter
        $response = $this->post('/connexion', [
            'email'    => $user->email,
            'password' => 'bonmotdepasse', // bon mot de passe, mais bloqué quand même
        ]);

        $response->assertSessionHasErrors('email');
        // Vérifier que le message mentionne le délai
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Trop de tentatives', $errors[0]);
    }

    // ── Email non vérifié bloqué des routes protégées ─────────────────────────

    public function test_utilisateur_non_verifie_bloque_des_routes_protegees(): void
    {
        $client = User::factory()->create([
            'role'              => 'client',
            'email_verified_at' => null, // non vérifié
            'is_active'         => true,
        ]);

        $this->actingAs($client)
             ->get(route('client.dashboard'))
             ->assertRedirect(route('email.notice'));
    }

    public function test_compte_seed_non_verifie_peut_acceder_avec_message(): void
    {
        $seeded = $this->makeUser('client');
        $seeded->forceFill(['is_seeded' => true, 'email_verified_at' => null])->save();

        $this->actingAs($seeded)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSessionHas('warning', 'Compte de démonstration : la vérification email est désactivée.');
    }

    // ── Actions admin sécurisées ──────────────────────────────────────────────

    public function test_client_ne_peut_pas_suspendre_un_utilisateur(): void
    {
        $client  = $this->makeUser('client');
        $victime = $this->makeUser('artisan');

        // La route admin de suspension ne doit pas être accessible à un client
        $this->actingAs($client)
             ->patch(route('admin.users.toggle', $victime))
             ->assertForbidden();
    }

    public function test_client_ne_peut_pas_acceder_aux_wallets_admin(): void
    {
        $client = $this->makeUser('client');

        $this->actingAs($client)
             ->get(route('admin.wallet.index'))
             ->assertForbidden();
    }

    public function test_admin_wallet_ne_declenche_pas_de_requetes_non_supportees(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.wallet.index'))
            ->assertOk();
    }

    public function test_compte_suspendu_est_bloque_sur_une_requete_authentifiee(): void
    {
        $client = $this->makeUser('client');
        $client->forceFill(['is_active' => false])->save();
        $client->refresh();

        $this->actingAs($client)
            ->get(route('client.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_retour_paiement_exige_une_authentification(): void
    {
        $this->get(route('payment.success', ['order' => 1]))
            ->assertRedirect(route('login'));
    }
}
