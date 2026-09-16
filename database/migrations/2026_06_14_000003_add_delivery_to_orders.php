<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_id')->nullable()
                  ->after('contract_path')
                  ->references('id')->on('deliveries')
                  ->nullOnDelete();
            $table->string('delivery_city')->nullable()->after('delivery_id');
            $table->boolean('needs_delivery')->default(false)->after('delivery_city');
        });
    }
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_id']);
            $table->dropColumn(['delivery_id','delivery_city','needs_delivery']);
        });
    }
};
