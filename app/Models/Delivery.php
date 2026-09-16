<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 'livreur_id', 'status',
        'pickup_city', 'delivery_city',
        'pickup_address', 'delivery_address',
        'pickup_lat', 'pickup_lng', 'delivery_lat', 'delivery_lng',
        'livreur_lat', 'livreur_lng', 'location_updated_at',
        'fee', 'notes',
        'accepted_at', 'picked_up_at', 'delivered_at',
        'excluded_livreur_ids',
        'proof_code_hash', 'proof_verified_at', 'proof_method',
        'proof_code',
        'proof_signature_path',
    ];

    protected $casts = [
        'fee'                   => 'decimal:0',
        'pickup_lat'            => 'float',
        'pickup_lng'            => 'float',
        'delivery_lat'          => 'float',
        'delivery_lng'          => 'float',
        'livreur_lat'           => 'float',
        'livreur_lng'           => 'float',
        'location_updated_at'   => 'datetime',
        'accepted_at'           => 'datetime',
        'picked_up_at'          => 'datetime',
        'delivered_at'          => 'datetime',
        'proof_verified_at'     => 'datetime',
        'proof_code'            => 'encrypted',
        'excluded_livreur_ids'  => 'array',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────
    public function order()   { return $this->belongsTo(Order::class); }
    public function livreur() { return $this->belongsTo(User::class, 'livreur_id'); }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function statusLabel(): string
    {
        return match($this->status) {
            'en_recherche' => 'Recherche livreur',
            'assignee'     => 'Livreur notifié',
            'acceptee'     => 'Mission acceptée',
            'en_route'     => 'En route',
            'recuperee'    => 'Objet récupéré',
            'livree'       => 'Livré ✅',
            'echouee'      => 'Échec ❌',
            default        => $this->status,
        };
    }
public function isSearching(): bool
{
    return $this->status === 'en_recherche';
}
    public function statusColor(): string
    {
        return match($this->status) {
            'en_recherche' => '#FFF3CD',
            'assignee'     => '#CFF4FC',
            'acceptee'     => '#CCE5FF',
            'en_route'     => '#F5EFE6',
            'recuperee'    => '#F5EFE6',
            'livree'       => '#D4EDDA',
            'echouee'      => '#F8D7DA',
            default        => '#F5EFE6',
        };
    }

    public function statusTextColor(): string
    {
        return match($this->status) {
            'en_recherche' => '#856404',
            'assignee'     => '#055160',
            'acceptee'     => '#004085',
            'en_route'     => '#C4622D',
            'recuperee'    => '#C4622D',
            'livree'       => '#155724',
            'echouee'      => '#721C24',
            default        => '#2C1A0E',
        };
    }

    public function canBeAccepted():  bool { return $this->status === 'assignee'; }
    public function canBePickedUp():  bool { return $this->status === 'acceptee'; }
    public function canBeStarted():   bool { return $this->status === 'acceptee'; }
    public function canBePickedUpFromArtisan(): bool { return $this->status === 'en_route'; }
    public function canBeDelivered(): bool { return $this->status === 'recuperee'; }

    public function hasDeliveryProof(): bool
    {
        return !is_null($this->proof_verified_at) && filled($this->proof_method);
    }

    /** La position du livreur peut être suivie en direct tant que la mission est active. */
    public function isTrackable(): bool
    {
        return in_array($this->status, ['acceptee', 'en_route', 'recuperee']);
    }

    public function hasLiveLocation(): bool
    {
        return !is_null($this->livreur_lat) && !is_null($this->livreur_lng);
    }
}
