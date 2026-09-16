<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    const STATUS_PENDING   = 'pending';
    const STATUS_ACCEPTED  = 'accepted';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_COUNTERED = 'countered'; // remplacé par une proposition suivante

    /** Au-delà de ce nombre de rounds, plus personne ne peut contre-proposer
     *  (évite les négociations sans fin) — la commande doit être annulée ou
     *  acceptée telle quelle. */
    const MAX_ROUNDS = 6;

    /** Une proposition sans réponse expire après ce délai. */
    const EXPIRY_HOURS = 48;

    protected $fillable = [
        'order_id', 'proposed_by_id', 'proposed_by_role',
        'amount', 'message', 'status', 'round', 'expires_at', 'auto_expired',
        'price_anomaly_note',
    ];

    protected $casts = [
        'amount'     => 'integer',
        'round'      => 'integer',
        'expires_at' => 'datetime',
        'auto_expired' => 'boolean',
    ];

    public function order()      { return $this->belongsTo(Order::class); }
    public function proposedBy() { return $this->belongsTo(User::class, 'proposed_by_id'); }

    public function isPending():  bool { return $this->status === self::STATUS_PENDING; }

    /** Vrai si la proposition est encore en attente mais que le délai est dépassé. */
    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at && $this->expires_at->isPast();
    }

    /** Une proposition n'est "active" (affichable comme en attente de réponse) que
     *  si elle est pending ET pas encore expirée. */
    public function isActive(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }

    /** Formaté pour affichage (XOF). */
    public function formattedAmount(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' XOF';
    }
}
