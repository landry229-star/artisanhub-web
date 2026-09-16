<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Position GPS en direct du livreur pendant la mission (distincte
            // de la position "domicile" statique déjà stockée sur users).
            $table->decimal('livreur_lat', 10, 7)->nullable()->after('notes');
            $table->decimal('livreur_lng', 10, 7)->nullable()->after('livreur_lat');
            $table->timestamp('location_updated_at')->nullable()->after('livreur_lng');

            // Coordonnées de la commune (fallback / centrage carte) pour le
            // point de retrait et le point de livraison.
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('pickup_address');
            $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
            $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_address');
            $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'livreur_lat', 'livreur_lng', 'location_updated_at',
                'pickup_lat', 'pickup_lng', 'delivery_lat', 'delivery_lng',
            ]);
        });
    }
};
