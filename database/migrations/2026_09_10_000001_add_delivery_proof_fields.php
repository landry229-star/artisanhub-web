<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('proof_code_hash')->nullable()->after('notes');
            $table->text('proof_code')->nullable()->after('proof_code_hash');
            $table->string('proof_signature_path')->nullable()->after('proof_code');
            $table->timestamp('proof_verified_at')->nullable()->after('delivered_at');
            $table->string('proof_method')->nullable()->after('proof_verified_at');
            $table->index(['delivery_city', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['city', 'quartier']);
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['delivery_city', 'status']);
            $table->dropColumn(['proof_code_hash', 'proof_code', 'proof_signature_path', 'proof_verified_at', 'proof_method']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['city', 'quartier']);
        });
    }
};
