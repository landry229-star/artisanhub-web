<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ArtisanProfile;
use App\Models\Order;
use App\Models\Review;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────────────────
        User::forceCreate([
            'name'        => 'Admin ArtisanHub',
            'email'       => 'admin@artisanhub.bj',
            'password'    => Hash::make('password'),
            'role'        => 'admin',
            'city'        => 'Cotonou',
            'is_verified' => true,
            'is_active'   => true,
            'is_seeded'   => true,
        ]);

        // ── Artisans ───────────────────────────────────────────────────────
        $artisans = [
            ['name'=>'Kouamé Diallo',    'city'=>'Cotonou',     'specialty'=>'Sculpteur sur bois',    'category'=>'menuiserie', 'rating'=>4.9, 'reviews'=>38],
            ['name'=>'Aïssatou Traoré',  'city'=>'Porto-Novo',  'specialty'=>'Créatrice de pagnes',   'category'=>'tissage',    'rating'=>4.8, 'reviews'=>61],
            ['name'=>'Moussa Kéïta',     'city'=>'Parakou',     'specialty'=>'Forgeron d\'art',       'category'=>'forge',      'rating'=>4.7, 'reviews'=>27],
            ['name'=>'Fatou Sow',        'city'=>'Abomey',      'specialty'=>'Bijoutière artisanale', 'category'=>'bijouterie', 'rating'=>5.0, 'reviews'=>19],
            ['name'=>'Kofi Mensah',      'city'=>'Cotonou',     'specialty'=>'Maçon traditionnel',    'category'=>'maconnerie', 'rating'=>4.6, 'reviews'=>44],
            ['name'=>'Aminata Coulibaly','city'=>'Abomey-Calavi','specialty'=>'Couturière mode',      'category'=>'couture',    'rating'=>4.5, 'reviews'=>33],
        ];

        foreach ($artisans as $data) {
            $user = User::forceCreate([
                'name'        => $data['name'],
                'email'       => strtolower(str_replace(' ', '.', $data['name'])) . '@test.com',
                'password'    => Hash::make('password'),
                'role'        => 'artisan',
                'city'        => $data['city'],
                'is_verified' => true,
                'is_active'   => true,
                'is_seeded'   => true,
            ]);

            ArtisanProfile::create([
                'user_id'       => $user->id,
                'specialty'     => $data['specialty'],
                'category'      => $data['category'],
                'bio'           => "Artisan passionné avec plus de 10 ans d'expérience dans {$data['specialty']}.",
                'hourly_rate'   => rand(3000, 8000),
                'rating'        => $data['rating'],
                'reviews_count' => $data['reviews'],
                'is_available'  => true,
            ]);
        }

        // ── Clients ───────────────────────────────────────────────────────
        $clients = [
            ['name'=>'Jean-Baptiste Alofa', 'city'=>'Cotonou'],
            ['name'=>'Marie Dossou',        'city'=>'Porto-Novo'],
            ['name'=>'Paul Agbossou',       'city'=>'Abomey-Calavi'],
        ];

        foreach ($clients as $data) {
            User::forceCreate([
                'name'      => $data['name'],
                'email'     => strtolower(str_replace([' ', '-'], '.', $data['name'])) . '@test.com',
                'password'  => Hash::make('password'),
                'role'      => 'client',
                'city'      => $data['city'],
                'is_active' => true,
                'is_seeded'   => true,
            ]);
        }

        // ── Livreurs ───────────────────────────────────────────────────────
        $livreurs = [
            ['name' => 'Thomas Agossou', 'city' => 'Cotonou', 'phone' => '0022967000101'],
            ['name' => 'Awa Soglo', 'city' => 'Porto-Novo', 'phone' => '0022967000102'],
        ];

        foreach ($livreurs as $data) {
            User::forceCreate([
                'name' => $data['name'],
                'email' => strtolower(str_replace(' ', '.', $data['name'])) . '@test.com',
                'password' => Hash::make('password'),
                'role' => 'livreur',
                'city' => $data['city'],
                'phone' => $data['phone'],
                'is_active' => true,
                'is_livreur_available' => true,
                'is_seeded' => true,
            ]);
        }

        $this->command->info('✅  Base de données initialisée avec succès !');
        $this->command->info('📧  Admin : admin@artisanhub.bj / password');
        $this->command->info('🚚  Livreurs : thomas.agossou@test.com et awa.soglo@test.com / password');
    }
}
