<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pièce d'identité
            $table->string('id_document_path')->nullable()->after('avatar');
            $table->enum('id_document_type', ['cni', 'passeport', 'permis'])->nullable()->after('id_document_path');
            $table->enum('id_document_status', ['none', 'pending', 'approved', 'rejected'])
                  ->default('none')->after('id_document_type');
            $table->string('id_document_rejected_reason')->nullable()->after('id_document_status');
            $table->timestamp('id_document_reviewed_at')->nullable()->after('id_document_rejected_reason');

            // Vérification du numéro de téléphone par OTP SMS
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('phone_otp_code', 6)->nullable()->after('phone_verified_at');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp_code');
            $table->unsignedTinyInteger('phone_otp_attempts')->default(0)->after('phone_otp_expires_at');

            // Préférence de canal (utilisé aussi par la notif WhatsApp/SMS)
            $table->boolean('whatsapp_opt_in')->default(true)->after('phone_otp_attempts');

            // Géolocalisation basique
            $table->string('quartier', 120)->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('quartier');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_document_path', 'id_document_type', 'id_document_status',
                'id_document_rejected_reason', 'id_document_reviewed_at',
                'phone_verified_at', 'phone_otp_code', 'phone_otp_expires_at', 'phone_otp_attempts',
                'whatsapp_opt_in', 'quartier', 'latitude', 'longitude',
            ]);
        });
    }
};
