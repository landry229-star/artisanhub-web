<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renforce en base une règle déjà supposée par le code applicatif :
 * - une commande n'a qu'UN SEUL paiement (Order::payment() est un hasOne,
 *   FedaPayService::createTransaction() fait un updateOrCreate(['order_id'=>...]))
 * - une commande n'a qu'UN SEUL avis (ReviewController::store() vérifie
 *   $order->review()->exists() avant création)
 *
 * Sans contrainte unique en base, un double-clic ou deux requêtes
 * concurrentes sur la même commande peuvent créer deux lignes payments (ou
 * reviews) pour le même order_id, cassant l'hypothèse 1-1 utilisée partout
 * ailleurs (ex: $order->payment retournerait une ligne arbitraire).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unique('order_id');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
        });
    }
};
