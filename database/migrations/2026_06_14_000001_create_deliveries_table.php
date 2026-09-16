<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livreur_id')->nullable()
                  ->references('id')->on('users')->nullOnDelete();
            $table->enum('status', [
                'en_recherche', // système cherche un livreur
                'assignee',     // livreur notifié
                'acceptee',     // livreur a accepté
                'en_route',     // livreur en chemin vers l'artisan
                'recuperee',    // livreur a récupéré l'objet
                'livree',       // livré chez le client
                'echouee',      // échec
            ])->default('en_recherche');
            $table->string('pickup_city');       // ville artisan
            $table->string('delivery_city');     // ville client
            $table->string('pickup_address')->nullable();
            $table->string('delivery_address')->nullable();
            $table->decimal('fee', 10, 2)->default(1000); // frais de livraison XOF
            $table->text('notes')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('deliveries'); }
};
