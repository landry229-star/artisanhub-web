<?php
namespace Database\Factories;

use App\Models\ArtisanProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtisanProfileFactory extends Factory
{
    protected $model = ArtisanProfile::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory()->artisan(),
            'specialty'         => fake()->randomElement([
                'Plomberie', 'Électricité', 'Menuiserie', 'Peinture', 'Maçonnerie',
            ]),
            'category'          => fake()->randomElement([
                'Batiment', 'Electronique', 'Artisanat',
            ]),
            'bio'               => fake()->paragraph(),
            'is_available'      => true,
            'rating'            => fake()->randomFloat(1, 3.0, 5.0),
            'reviews_count'     => fake()->numberBetween(0, 50),
        ];
    }
}
