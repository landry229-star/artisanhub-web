<?php
namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'                      => fake()->name(),
            'email'                     => fake()->unique()->safeEmail(),
            'password'                  => bcrypt('motdepasse123'),
            'role'                      => 'client',
            'phone'                     => '0022967' . fake()->numerify('#######'),
            'city'                      => 'Cotonou',
            'is_active'                 => true,
            'email_verified_at'         => now(),
            'email_verification_token'  => null,
        ];
    }

    public function artisan(): static
    {
        return $this->state(['role' => 'artisan']);
    }

    public function client(): static
    {
        return $this->state(['role' => 'client']);
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function livreur(): static
    {
        return $this->state(['role' => 'livreur']);
    }

    public function nonVerifie(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function suspendu(): static
    {
        return $this->state(['is_active' => false]);
    }
}
