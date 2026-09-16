<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArtisanProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'specialty', 'category', 'city', 'bio',
        'hourly_rate', 'rating', 'reviews_count',
        'is_available', 'available_for_delivery',
        'tier', 'completed_orders_count',
        'years_experience', 'typical_delivery_days', 'service_radius_km',
        'materials', 'languages',
    ];

    protected $casts = [
        'is_available'           => 'boolean',
        'available_for_delivery' => 'boolean',
        'hourly_rate'            => 'decimal:0',
        'rating'                 => 'float',
    ];

    // ── Paliers de confiance ──────────────────────────────────────────────────
    const TIER_DEBUTANT = 'debutant';
    const TIER_CONFIRME = 'confirme';
    const TIER_EXPERT   = 'expert';

    // Seuils de passage de palier. Le taux de litige protège contre un
    // artisan qui accumule du volume mais génère beaucoup de conflits.
    const CONFIRME_MIN_ORDERS      = 5;
    const CONFIRME_MIN_RATING      = 4.0;
    const EXPERT_MIN_ORDERS        = 20;
    const EXPERT_MIN_RATING        = 4.5;
    const EXPERT_MAX_DISPUTE_RATE  = 5.0; // %

    // Part du net artisan mise de côté dans le fonds de garantie (Expert uniquement)
    const GUARANTEE_CONTRIBUTION_RATE = 0.01; // 1%

    // ── Relations ─────────────────────────────────────────────────────────────
    public function user()           { return $this->belongsTo(User::class); }
    public function portfolioItems() { return $this->hasMany(PortfolioItem::class); }
    public function services()       { return $this->hasMany(Service::class); }
    public function activeServices() { return $this->hasMany(Service::class)->where('is_active', true); }

    // ── Badge visuel existant (conservé, inchangé) ──────────────────────────────
    public function badge(): string
    {
        if ($this->rating >= 4.8 && $this->reviews_count >= 20) return 'Top Artisan';
        if ($this->user->is_verified) return 'Vérifié';
        return 'Nouveau';
    }

    public function badgeColor(): string
    {
        return match($this->badge()) {
            'Top Artisan' => 'danger',
            'Vérifié'     => 'success',
            default       => 'warning',
        };
    }

    // ── Palier de confiance (nouveau) ───────────────────────────────────────────
    public function tierLabel(): string
    {
        return match($this->tier) {
            self::TIER_EXPERT   => 'Expert',
            self::TIER_CONFIRME => 'Confirmé',
            default             => 'Débutant',
        };
    }

    public function tierColor(): string
    {
        return match($this->tier) {
            self::TIER_EXPERT   => '#B8860B', // or
            self::TIER_CONFIRME => '#0D6EFD', // bleu
            default             => '#6C757D', // gris
        };
    }

    /**
     * Seul un artisan Expert ET identité vérifiée (KYC : pièce d'identité +
     * numéro de téléphone confirmés) peut proposer la garantie satisfait/repris.
     * Sans cette double condition, le fonds de garantie n'a aucune valeur
     * probante en cas de litige (impossible de retrouver l'artisan).
     */
    public function canOfferGuarantee(): bool
    {
        return $this->tier === self::TIER_EXPERT && $this->user->isFullyVerified();
    }

    /**
     * Recalcule le palier de confiance de l'artisan à partir de :
     * - nombre de commandes terminées
     * - note moyenne
     * - taux de litige (commandes en litige / (terminées + litiges))
     *
     * À appeler après chaque commande terminée ET après chaque avis reçu,
     * car les deux entrent dans le calcul.
     */
    public function recalculateTier(): void
    {
        $completed = Order::where('artisan_id', $this->user_id)
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        $disputed = Order::where('artisan_id', $this->user_id)
            ->where('status', Order::STATUS_DISPUTED)
            ->count();

        $relevant    = $completed + $disputed;
        $disputeRate = $relevant > 0 ? ($disputed / $relevant) * 100 : 0;

        $tier = match(true) {
            $completed >= self::EXPERT_MIN_ORDERS
                && $this->rating >= self::EXPERT_MIN_RATING
                && $disputeRate <= self::EXPERT_MAX_DISPUTE_RATE
                => self::TIER_EXPERT,

            $completed >= self::CONFIRME_MIN_ORDERS
                && $this->rating >= self::CONFIRME_MIN_RATING
                => self::TIER_CONFIRME,

            default => self::TIER_DEBUTANT,
        };

        $this->update([
            'tier'                    => $tier,
            'completed_orders_count'  => $completed,
        ]);
    }

    // ── Recalcul note (existant, étendu pour recalculer aussi le palier) ────────
    public function recalculateRating(): void
    {
        $reviews = Review::where('artisan_id', $this->user_id);
        $count = (clone $reviews)->count();
        if ($count === 0) return;
        $this->update([
            'rating'        => round((float) $reviews->avg('rating'), 1),
            'reviews_count' => $count,
        ]);
        $this->recalculateTier();
    }
}
