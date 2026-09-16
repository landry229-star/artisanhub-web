<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Historique cumulatif de tous les livreurs déjà exclus pour
            // cette livraison (refus explicite ou échec signalé). Sans ça,
            // reassign() ne pouvait exclure que le tout dernier livreur, ce
            // qui permettait de re-proposer la mission à un livreur qui
            // avait déjà refusé — problématique dans les communes avec peu
            // de livreurs disponibles.
            $table->json('excluded_livreur_ids')->nullable()->after('livreur_id');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('excluded_livreur_ids');
        });
    }
};
