<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Photo obligatoire du travail terminé, prise au moment où
            // l'artisan marque la commande "livrée". Sert à la fois de
            // preuve en cas de litige et alimente automatiquement le
            // portfolio de l'artisan.
            $table->string('completion_photo_path')->nullable()->after('contract_path');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completion_photo_path');
        });
    }
};
