<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                // NB: morphs() crée déjà l'index (notifiable_type, notifiable_id) ;
                // l'appel explicite ci-dessous causait une erreur "index already exists"
                // sur une installation fraîche (corrigé).
            });
        }
    }
    public function down(): void { Schema::dropIfExists('notifications'); }
};
