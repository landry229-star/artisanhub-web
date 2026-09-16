<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('fedapay_payout_id')->nullable()->after('fedapay_transaction_id');
            $table->string('refund_status')->nullable()->after('refund_note');
            $table->text('refund_error')->nullable()->after('refund_status');
            $table->index('fedapay_payout_id');
        });

        Schema::table('guarantee_claims', function (Blueprint $table) {
            $table->string('fedapay_payout_id')->nullable()->after('refund_amount');
            $table->string('refund_status')->nullable()->after('fedapay_payout_id');
            $table->text('refund_error')->nullable()->after('refund_status');
            $table->index('fedapay_payout_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['fedapay_payout_id']);
            $table->dropColumn(['fedapay_payout_id', 'refund_status', 'refund_error']);
        });

        Schema::table('guarantee_claims', function (Blueprint $table) {
            $table->dropIndex(['fedapay_payout_id']);
            $table->dropColumn(['fedapay_payout_id', 'refund_status', 'refund_error']);
        });
    }
};
