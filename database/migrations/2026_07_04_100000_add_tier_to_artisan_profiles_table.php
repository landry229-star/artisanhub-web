<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            // Palier de confiance : debutant / confirme / expert.
            // Recalculé automatiquement (recalculateTier()) après chaque
            // commande terminée et chaque avis reçu. Mis en cache ici en
            // colonne pour éviter de recalculer à chaque affichage de fiche.
            $table->string('tier')->default('debutant')->after('reviews_count');
            $table->unsignedInteger('completed_orders_count')->default(0)->after('tier');
        });
    }

    public function down(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            $table->dropColumn(['tier', 'completed_orders_count']);
        });
    }
};
