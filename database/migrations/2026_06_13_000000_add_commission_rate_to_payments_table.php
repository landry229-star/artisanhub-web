<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Taux appliqué au moment du paiement (0.05 / 0.08 / 0.10)
            $table->float('commission_rate', 4, 2)->default(0.05)->after('net_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
