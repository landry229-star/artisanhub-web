<?php
namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'client_id'      => User::factory()->client(),
            'artisan_id'     => User::factory()->artisan(),
            'title'          => fake()->sentence(5),
            'description'    => fake()->paragraph(3),
            'budget'         => fake()->numberBetween(5_000, 200_000),
            'status'         => Order::STATUS_PENDING,
            'deadline'       => now()->addDays(fake()->numberBetween(3, 30)),
            'needs_delivery' => false,
        ];
    }

    public function enAttente(): static
    {
        return $this->state(['status' => Order::STATUS_PENDING]);
    }

    public function acceptee(): static
    {
        return $this->state(['status' => Order::STATUS_ACCEPTED]);
    }

    public function enCours(): static
    {
        return $this->state(['status' => Order::STATUS_IN_PROGRESS]);
    }

    public function livree(): static
    {
        return $this->state(['status' => Order::STATUS_DELIVERED]);
    }

    public function terminee(): static
    {
        return $this->state(['status' => Order::STATUS_COMPLETED]);
    }

    public function avecLivraison(): static
    {
        return $this->state([
            'needs_delivery' => true,
            'delivery_city'  => 'Cotonou',
        ]);
    }
}
