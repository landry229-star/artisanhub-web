<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('artisan_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['client_id','artisan_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('favorites'); }
};
