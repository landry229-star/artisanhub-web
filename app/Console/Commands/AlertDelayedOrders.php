<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderDelayed;
use Illuminate\Console\Command;

class AlertDelayedOrders extends Command
{
    protected $signature = 'orders:alert-delayed';
    protected $description = 'Notifie les parties des commandes dont le délai est dépassé';

    public function handle(): int
    {
        $count = 0;
        Order::whereIn('status', [Order::STATUS_ACCEPTED, Order::STATUS_IN_PROGRESS])
            ->whereDate('deadline', '<', today())
            ->whereNull('delay_alerted_at')
            ->with(['client', 'artisan'])
            ->chunkById(100, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    $order->client->notify(new OrderDelayed($order));
                    $order->artisan->notify(new OrderDelayed($order));
                    $order->update(['delay_alerted_at' => now()]);
                    $count++;
                }
            });

        $this->info("{$count} commande(s) en retard signalée(s).");
        return self::SUCCESS;
    }
}
