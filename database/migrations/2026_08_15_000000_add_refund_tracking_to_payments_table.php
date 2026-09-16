<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remboursement demandé par un admin (arbitrage litige) : le
            // transfert réel se fait manuellement via le dashboard FedaPay
            // (MTN Mobile Money uniquement — aucun endpoint API fiable pour
            // ça), donc on trace la demande puis la confirmation séparément.
            $table->timestamp('refund_requested_at')->nullable()->after('reversal_note');
            $table->foreignId('refund_requested_by')->nullable()->after('refund_requested_at')
                  ->constrained('users')->nullOnDelete();
            $table->string('refund_note')->nullable()->after('refund_requested_by');

            $table->timestamp('refunded_at')->nullable()->after('refund_note');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['refund_requested_by']);
            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['refund_requested_at', 'refund_requested_by', 'refund_note', 'refunded_at', 'refunded_by']);
        });
    }
};
