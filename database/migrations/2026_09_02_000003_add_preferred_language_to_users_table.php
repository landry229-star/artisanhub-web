<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Langue préférée pour la messagerie (traduction auto des
            // messages texte et des transcriptions vocales). 'fr' par défaut
            // — cohérent avec le reste de la plateforme.
            $table->string('preferred_language', 10)->default('fr')->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_language');
        });
    }
};
