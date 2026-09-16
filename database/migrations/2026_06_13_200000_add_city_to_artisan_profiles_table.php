<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('category');
            $table->boolean('available_for_delivery')->default(false)->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            $table->dropColumn(['city', 'available_for_delivery']);
        });
    }
};
