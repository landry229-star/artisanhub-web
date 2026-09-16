<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artisan_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialty');
            $table->string('category');
            $table->text('bio')->nullable();
            $table->unsignedInteger('hourly_rate')->nullable();
            $table->float('rating', 3, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('artisan_profiles'); }
};
