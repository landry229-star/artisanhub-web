<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guarantee_claims', function (Blueprint $table) {
            // Même trou que sur Payment (arbitrage litige) : "approved" ne
            // veut dire que la décision est prise, pas que l'argent a
            // bougé — FedaPay n'a pas d'API de remboursement fiable, c'est
            // fait à la main sur leur dashboard puis confirmé ici.
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guarantee_claims', function (Blueprint $table) {
            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['refunded_at', 'refunded_by']);
        });
    }
};
