<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // is_active — Bug 4 : s'assurer qu'elle existe
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_verified');
            }
            // is_livreur_available
            if (!Schema::hasColumn('users', 'is_livreur_available')) {
                $table->boolean('is_livreur_available')->default(true)->after('is_active');
            }
            // delivery_address
            if (!Schema::hasColumn('users', 'delivery_address')) {
                $table->string('delivery_address')->nullable()->after('city');
            }
        });
    }
    public function down(): void {}
};
