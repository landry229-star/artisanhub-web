<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Historique des propositions de prix (devis) sur une commande.
// Une commande peut avoir plusieurs "rounds" de négociation avant accord.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proposed_by_id')->constrained('users')->cascadeOnDelete();
            $table->enum('proposed_by_role', ['client', 'artisan']);
            $table->unsignedBigInteger('amount');
            $table->string('message', 500)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'countered'])->default('pending');
            $table->unsignedTinyInteger('round')->default(1);
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        // État de négociation lisible directement sur la commande.
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('negotiation_status', ['none', 'in_progress', 'agreed'])
                  ->default('none')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('negotiation_status');
        });
        Schema::dropIfExists('quotes');
    }
};
