<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ArtisanProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Inscription ───────────────────────────────────────────────────────────

    public function test_client_peut_sinscrire_avec_donnees_valides(): void
    {
        Mail::fake();

        $response = $this->post('/inscription', [
            'name'                  => 'Jean Dupont',
            'email'                 => 'jean@example.com',
            'password'              => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role'                  => 'client',
            'phone'                 => '0022967000000',
            'city'                  => 'Cotonou',
        ]);

        $response->assertRedirect(route('email.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'jean@example.com',
            'role'  => 'client',
        ]);
    }

    public function test_artisan_peut_sinscrire_avec_specialite(): void
    {
        Mail::fake();

        $this->post('/inscription', [
            'name'                  => 'Kofi Plombier',
            'email'                 => 'kofi@example.com',
            'password'              => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role'                  => 'artisan',
            'phone'                 => '0022967000001',
            'city'                  => 'Cotonou',
            'specialty'             => 'Plomberie',
            'category'              => 'Batiment',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'kofi@example.com', 'role' => 'artisan']);
        $user = User::where('email', 'kofi@example.com')->first();
        $this->assertNotNull($user->artisanProfile);
    }

    public function test_inscription_echoue_si_email_deja_utilise(): void
    {
        User::factory()->create(['email' => 'existe@example.com']);

        $response = $this->post('/inscription', [
            'name'                  => 'Autre',
            'email'                 => 'existe@example.com',
            'password'              => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role'                  => 'client',
            'city'                  => 'Cotonou',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_inscription_echoue_si_mots_de_passe_differents(): void
    {
        $response = $this->post('/inscription', [
            'name'                  => 'Jean',
            'email'                 => 'jean2@example.com',
            'password'              => 'motdepasse123',
            'password_confirmation' => 'autrechose',
            'role'                  => 'client',
            'city'                  => 'Cotonou',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_inscription_echoue_si_role_invalide(): void
    {
        $response = $this->post('/inscription', [
            'name'                  => 'Jean',
            'email'                 => 'jean3@example.com',
            'password'              => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role'                  => 'superadmin', // rôle non autorisé
            'city'                  => 'Cotonou',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_artisan_sans_specialite_ne_peut_pas_sinscrire(): void
    {
        $response = $this->post('/inscription', [
            'name'                  => 'Artisan',
            'email'                 => 'artisan@example.com',
            'password'              => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role'                  => 'artisan',
            'city'                  => 'Cotonou',
            // specialty et category absents
        ]);

        $response->assertSessionHasErrors(['specialty', 'category']);
    }

    // ── Connexion ─────────────────────────────────────────────────────────────

    public function test_utilisateur_peut_se_connecter(): void
    {
        $user = User::factory()->create([
            'password'          => bcrypt('motdepasse123'),
            'role'              => 'client',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $response = $this->post('/connexion', [
            'email'    => $user->email,
            'password' => 'motdepasse123',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_connexion_echoue_avec_mauvais_mot_de_passe(): void
    {
        $user = User::factory()->create(['password' => bcrypt('bonmotdepasse')]);

        $response = $this->post('/connexion', [
            'email'    => $user->email,
            'password' => 'mauvaimotdepasse',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_compte_suspendu_ne_peut_pas_se_connecter(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('motdepasse123'),
            'is_active' => false,
        ]);

        $response = $this->post('/connexion', [
            'email'    => $user->email,
            'password' => 'motdepasse123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_non_verifie_redirige_vers_notice(): void
    {
        $user = User::factory()->create([
            'password'          => bcrypt('motdepasse123'),
            'email_verified_at' => null,
            'is_active'         => true,
        ]);

        $response = $this->post('/connexion', [
            'email'    => $user->email,
            'password' => 'motdepasse123',
        ]);

        $response->assertRedirect(route('email.notice'));
    }

    public function test_deconnexion_fonctionne(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post('/deconnexion');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    // ── Protection des routes ─────────────────────────────────────────────────

    public function test_dashboard_client_inaccessible_sans_connexion(): void
    {
        $this->get(route('client.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_artisan_inaccessible_pour_un_client(): void
    {
        $client = User::factory()->create([
            'role'              => 'client',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($client)
             ->get(route('artisan.dashboard'))
             ->assertForbidden();
    }
}
