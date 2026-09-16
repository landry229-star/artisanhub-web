<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Part du net de l'artisan mise de côté dans le fonds de
            // garantie "satisfait ou repris" — uniquement prélevée pour
            // les artisans au palier Expert (seuls habilités à offrir
            // cette garantie). Déjà déduite de net_amount au calcul.
            $table->unsignedInteger('guarantee_contribution')->default(0)->after('net_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('guarantee_contribution');
        });
    }
};
