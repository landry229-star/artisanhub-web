<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'order_id', 'sender_id', 'recipient_id', 'body', 'attachment_path', 'read_at',
        'audio_path', 'audio_duration', 'audio_transcript',
        'translated_body', 'translated_lang',
        'is_flagged', 'flag_reason', 'flagged_by', 'flagged_at',
    ];

    protected $casts = [
        'read_at'    => 'datetime',
        'flagged_at' => 'datetime',
        'is_flagged' => 'boolean',
    ];

    // Mots suspects détectés automatiquement
    const SUSPECT_KEYWORDS = [
        'whatsapp', 'watsap', 'telegram', 'tg', 'signal', 'viber',
        'payer en dehors', 'paye dehors', 'direct', 'hors plateforme',
        'virement', 'wave', 'momo', 'sans commission', 'sans frais',
        'mon numéro', 'mon numero', 'appelle-moi', 'appelle moi',
        'contacte-moi', 'contacte moi', 'arnaque', 'avance', 'avancer',
    ];

    public function order()     { return $this->belongsTo(Order::class); }
    public function sender()    { return $this->belongsTo(User::class, 'sender_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
    public function flaggedBy() { return $this->belongsTo(User::class, 'flagged_by'); }

    /**
     * Restreint aux messages échangés STRICTEMENT entre $a et $b — c'est ce
     * qui garantit que chaque conversation reste privée (le client ne voit
     * jamais les messages livreur↔artisan, etc).
     */
    public function scopeBetweenPair($query, int $a, int $b)
    {
        return $query->where(function ($q) use ($a, $b) {
            $q->where(['sender_id' => $a, 'recipient_id' => $b])
              ->orWhere(['sender_id' => $b, 'recipient_id' => $a]);
        });
    }

    public function isRead(): bool     { return !is_null($this->read_at); }
    public function markAsRead(): void { $this->update(['read_at' => now()]); }
    public function isFlagged(): bool  { return (bool) $this->is_flagged; }
    public function isAudio(): bool    { return !is_null($this->audio_path); }

    /** Texte à afficher pour le destinataire courant : traduction si dispo, sinon original. */
    public function displayTextFor(User $viewer): string
    {
        $original = $this->body ?: $this->audio_transcript ?: '';
        if ($viewer->id !== $this->sender_id && $this->translated_body) {
            return $this->translated_body;
        }
        return $original;
    }

    /**
     * Détecte si le corps contient un mot suspect.
     * Retourne le mot trouvé ou null.
     */
    public static function detectSuspect(string $body): ?string
    {
        $lower = mb_strtolower($body);
        foreach (self::SUSPECT_KEYWORDS as $keyword) {
            if (str_contains($lower, $keyword)) {
                return $keyword;
            }
        }
        return null;
    }
}
