<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('admin_note');
            $table->text('rejection_reason')->nullable()->after('cancellation_reason');
            $table->timestamp('delay_alerted_at')->nullable()->after('alerted_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'rejection_reason', 'delay_alerted_at']);
        });
    }
};
