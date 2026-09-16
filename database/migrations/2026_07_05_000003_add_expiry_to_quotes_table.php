<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('round');
            // Distingue un refus explicite (par l'autre partie) d'une expiration
            // automatique faute de réponse — utile pour l'historique/support.
            $table->boolean('auto_expired')->default(false)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'auto_expired']);
        });
    }
};
