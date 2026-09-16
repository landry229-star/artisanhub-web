<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Signalement des messages suspects
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('read_at');
            $table->string('flag_reason')->nullable()->after('is_flagged');
            $table->foreignId('flagged_by')->nullable()->after('flag_reason')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('flagged_at')->nullable()->after('flagged_by');
        });

        // Alerte admin sur une commande
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_alert')->default(false)->after('admin_note');
            $table->string('alert_message')->nullable()->after('has_alert');
            $table->timestamp('alerted_at')->nullable()->after('alert_message');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['flagged_by']);
            $table->dropColumn(['is_flagged','flag_reason','flagged_by','flagged_at']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_alert','alert_message','alerted_at']);
        });
    }
};
