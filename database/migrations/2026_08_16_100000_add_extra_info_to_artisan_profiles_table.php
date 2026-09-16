<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            // Années d'expérience déclarées par l'artisan (indépendant de
            // l'ancienneté sur la plateforme, qui est mesurée par le tier).
            $table->unsignedTinyInteger('years_experience')->nullable()->after('bio');

            // Délai de réalisation habituel, en jours, pour une commande type.
            $table->unsignedTinyInteger('typical_delivery_days')->nullable()->after('years_experience');

            // Rayon de déplacement pour une intervention chez le client, en km.
            // Distinct de available_for_delivery qui concerne la livraison de l'objet.
            $table->unsignedSmallInteger('service_radius_km')->nullable()->after('typical_delivery_days');

            // Matériaux / techniques principaux, texte libre court.
            $table->string('materials', 255)->nullable()->after('service_radius_km');

            // Langues parlées, texte libre court ("Français, Fon, Yoruba").
            $table->string('languages', 255)->nullable()->after('materials');
        });
    }

    public function down(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'years_experience',
                'typical_delivery_days',
                'service_radius_km',
                'materials',
                'languages',
            ]);
        });
    }
};
