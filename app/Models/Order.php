<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING     = 'en_attente';
    const STATUS_ACCEPTED    = 'acceptee';
    const STATUS_IN_PROGRESS = 'en_cours';
    const STATUS_DELIVERED   = 'livree';
    const STATUS_COMPLETED   = 'terminee';
    const STATUS_CANCELLED   = 'annulee';
    const STATUS_DISPUTED    = 'litige';

    protected $fillable = [
        'client_id', 'artisan_id', 'service_id', 'title', 'description',
        'budget', 'status', 'deadline', 'contract_path', 'admin_note',
        'needs_delivery', 'delivery_id', 'delivery_city',
        'has_alert', 'alert_message', 'alerted_at',
        'cancellation_reason', 'rejection_reason', 'delay_alerted_at',
    ];

    protected $casts = [
        'deadline'       => 'date',
        'budget'         => 'decimal:0',
        'needs_delivery' => 'boolean',
        'has_alert'      => 'boolean',
        'alerted_at'     => 'datetime',
        'delay_alerted_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────
    public function client()   { return $this->belongsTo(User::class, 'client_id'); }
    public function artisan()  { return $this->belongsTo(User::class, 'artisan_id'); }
    public function service()  { return $this->belongsTo(Service::class); }
    public function messages() { return $this->hasMany(Message::class); }
    public function payment()  { return $this->hasOne(Payment::class); }
    public function review()   { return $this->hasOne(Review::class); }
    public function delivery() { return $this->hasOne(Delivery::class); }
    public function guaranteeClaim() { return $this->hasOne(GuaranteeClaim::class); }
    public function quotes()   { return $this->hasMany(Quote::class)->orderByDesc('round'); }
    public function images()   { return $this->hasMany(OrderImage::class); }

    // ── Négociation (devis) ───────────────────────────────────────────────────
    const NEGOTIATION_NONE        = 'none';
    const NEGOTIATION_IN_PROGRESS = 'in_progress';
    const NEGOTIATION_AGREED      = 'agreed';

    /** Dernier devis en attente de réponse (le plus récent round) et non expiré, ou null. */
    public function currentQuote(): ?Quote
    {
        return $this->quotes()
            ->where('status', Quote::STATUS_PENDING)
            ->get()
            ->first(fn(Quote $q) => !$q->isExpired());
    }

    /** Une négociation peut démarrer tant que la commande n'est pas encore acceptée/annulée. */
    public function canProposeQuote(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING]);
    }

    /** Faux une fois le nombre maximum de rounds de négociation atteint. */
    public function canProposeMoreQuotes(): bool
    {
        return $this->canProposeQuote() && $this->quotes()->count() < Quote::MAX_ROUNDS;
    }

    /**
     * Garde-fou métier (non bloquant) : signale un montant de devis qui
     * s'écarte fortement d'une référence connue, sans jamais empêcher la
     * proposition — juste pour attirer l'attention du client/artisan et de
     * l'admin.
     *
     * Priorité de la référence :
     *  1. Prix catalogue du service (si la commande part d'un service à prix fixe)
     *  2. Montant du round précédent (évite les négociations en dents de scie)
     *
     * @return string|null Message d'alerte, ou null si rien d'anormal.
     */
    public function priceAnomalyWarning(int $amount): ?string
    {
        if ($this->service_id && $this->service && $this->service->price > 0) {
            $reference = $this->service->price;
            $deviation = abs($amount - $reference) / $reference;
            if ($deviation > 0.5) {
                return "Cette proposition s'écarte de plus de 50% du prix catalogue de ce service ({$this->service->formattedPrice()}).";
            }
            return null;
        }

        $lastQuote = $this->quotes()->orderByDesc('round')->first();
        if ($lastQuote && $lastQuote->amount > 0) {
            $ratio = $amount / $lastQuote->amount;
            if ($ratio > 3 || $ratio < (1 / 3)) {
                return "Cette proposition s'écarte fortement (plus de 3x) de la précédente ({$lastQuote->formattedAmount()}).";
            }
        }

        return null;
    }

    // ── Helpers statut ────────────────────────────────────────────────────────
    public function statusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING     => 'En attente',
            self::STATUS_ACCEPTED    => 'Acceptée',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_DELIVERED   => 'Livrée',
            self::STATUS_COMPLETED   => 'Terminée',
            self::STATUS_CANCELLED   => 'Annulée',
            self::STATUS_DISPUTED    => 'Litige',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING     => 'warning',
            self::STATUS_ACCEPTED    => 'info',
            self::STATUS_IN_PROGRESS => 'primary',
            self::STATUS_DELIVERED   => 'secondary',
            self::STATUS_COMPLETED   => 'success',
            self::STATUS_CANCELLED   => 'dark',
            self::STATUS_DISPUTED    => 'danger',
            default => 'secondary',
        };
    }

    // ── Transitions autorisées ────────────────────────────────────────────────
    public function canBeAccepted():  bool { return $this->status === self::STATUS_PENDING; }
    public function canBeStarted():   bool { return $this->status === self::STATUS_ACCEPTED; }
    public function canBeDelivered(): bool { return $this->status === self::STATUS_IN_PROGRESS; }
    public function canBeValidated(): bool { return $this->status === self::STATUS_DELIVERED; }
    public function canBeDisputed():  bool { return $this->status === self::STATUS_DELIVERED; }
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACCEPTED]);
    }

    /**
     * IDs des utilisateurs autorisés à participer à la messagerie de cette
     * commande : le client, l'artisan, et — s'il y a une livraison en cours
     * ou terminée — le livreur assigné (pour qu'il puisse communiquer avec
     * l'artisan pour récupérer le colis et avec le client pour la remise).
     */
    public function chatParticipantIds(): array
    {
        $ids = [$this->client_id, $this->artisan_id];

        $delivery = $this->relationLoaded('delivery') ? $this->delivery : $this->delivery()->first();
        if ($delivery && $delivery->livreur_id) {
            $ids[] = $delivery->livreur_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Les autres participants avec qui $user peut discuter séparément sur
     * cette commande (chaque paire a sa propre conversation privée — le
     * client ne voit jamais les échanges livreur↔artisan et inversement).
     */
    public function chatContactsFor(User $user): \Illuminate\Support\Collection
    {
        $ids = array_filter($this->chatParticipantIds(), fn ($id) => $id !== $user->id);
        return User::whereIn('id', $ids)->get();
    }

    /** Interlocuteur par défaut à ouvrir quand on arrive sans préciser de conversation. */
    public function defaultChatContactFor(User $user): ?User
    {
        if ($user->id === $this->client_id)  return $this->artisan;
        if ($user->id === $this->artisan_id) return $this->client;
        // Livreur (ou autre) : on ouvre en priorité la conversation avec l'artisan.
        return $this->artisan ?: $this->client;
    }
}
