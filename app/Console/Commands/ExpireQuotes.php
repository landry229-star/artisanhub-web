<?php
namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;

/**
 * Fait le ménage sur les devis restés en attente trop longtemps
 * (voir Quote::EXPIRY_HOURS) — évite qu'une négociation reste bloquée
 * indéfiniment sans qu'aucune des deux parties n'ait à agir.
 */
class ExpireQuotes extends Command
{
    protected $signature   = 'quotes:expire';
    protected $description = "Marque comme refusées les propositions de devis en attente dont le délai est dépassé";

    public function handle(): int
    {
        $count = 0;
        Quote::where('status', Quote::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->chunkById(500, function ($quotes) use (&$count) {
                foreach ($quotes as $quote) {
                    $quote->update([
                        'status'       => Quote::STATUS_REJECTED,
                        'auto_expired' => true,
                    ]);
                    $count++;
                }
            });

        $this->info("{$count} devis expiré(s) traité(s).");
        return self::SUCCESS;
    }
}
