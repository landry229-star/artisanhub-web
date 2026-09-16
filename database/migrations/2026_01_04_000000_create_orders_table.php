<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('artisan_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('budget')->nullable();
            $table->enum('status', [
                'en_attente','acceptee','en_cours',
                'livree','terminee','annulee','litige'
            ])->default('en_attente');
            $table->date('deadline')->nullable();
            $table->string('contract_path')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
