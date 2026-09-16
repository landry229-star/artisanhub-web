<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Autorise le corps du message à être vide quand il s'agit d'un
            // vocal seul (déjà nullable côté validation ; ici on le rend
            // vraiment optionnel en base pour les messages 100% audio).
            $table->text('body')->nullable()->change();

            $table->string('audio_path')->nullable()->after('attachment_path');
            $table->unsignedInteger('audio_duration')->nullable()->after('audio_path');

            // Traduction automatique du texte (et de la transcription audio)
            // dans la langue préférée du destinataire.
            $table->text('audio_transcript')->nullable()->after('audio_duration');
            $table->text('translated_body')->nullable()->after('audio_transcript');
            $table->string('translated_lang', 10)->nullable()->after('translated_body');

            // Le destinataire du message (livreur inclus sur les commandes
            // avec livraison) — permet de savoir qui doit voir le message
            // traduit sans devoir deviner à partir de order->client/artisan.
            $table->foreignId('recipient_id')->nullable()
                  ->after('sender_id')
                  ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'audio_path', 'audio_duration', 'audio_transcript',
                'translated_body', 'translated_lang', 'recipient_id',
            ]);
        });
    }
};
