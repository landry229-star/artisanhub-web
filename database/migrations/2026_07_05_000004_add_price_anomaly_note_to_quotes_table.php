<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Non bloquant : sert juste à afficher une alerte visuelle et à
            // informer l'admin qu'un montant s'écarte fortement d'une référence
            // (prix catalogue du service, ou round précédent de négociation).
            $table->string('price_anomaly_note')->nullable()->after('auto_expired');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('price_anomaly_note');
        });
    }
};
