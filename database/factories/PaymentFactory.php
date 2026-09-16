<?php
namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount     = fake()->numberBetween(5_000, 100_000);
        $rate       = 0.05;
        $commission = round($amount * $rate);

        return [
            'order_id'                => Order::factory(),
            'amount'                  => $amount,
            'commission_rate'         => $rate,
            'commission'              => $commission,
            'net_amount'              => $amount - $commission,
            'method'                  => 'mtn_mobile_money',
            'fedapay_transaction_id'  => 'TXN-' . fake()->numerify('########'),
            'status'                  => 'pending',
            'paid_at'                 => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status'  => 'completed',
            'paid_at' => now(),
        ]);
    }

    public function reversed(): static
    {
        return $this->state([
            'status'       => 'reversed',
            'reversed_at'  => now(),
        ]);
    }
 }
