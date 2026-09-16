<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

   protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'city',
    'country',
    'preferred_language',
    'avatar',
    'delivery_address',
    'is_livreur_available',
    'is_seeded',
    'email_verification_token',
    // Géolocalisation basique
    'quartier',
    'latitude',
    'longitude',
    // Préférence de notification
    'whatsapp_opt_in',
    'email_notifications',
    'order_notifications',
    // KYC léger
    'id_document_path',
    'id_document_type',
    'id_document_status',
    'id_document_rejected_reason',
    'id_document_reviewed_at',
    'phone_verified_at',
    'phone_otp_code',
    'phone_otp_expires_at',
    'phone_otp_attempts',
];
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'      => 'datetime',
        'is_verified'            => 'boolean',
        'is_active'              => 'boolean',
        'is_seeded'                 => 'boolean',
        'is_livreur_available'   => 'boolean',
        'whatsapp_opt_in'        => 'boolean',
        'email_notifications'   => 'boolean',
        'order_notifications'   => 'boolean',
        'latitude'               => 'float',
        'longitude'              => 'float',
        'phone_verified_at'      => 'datetime',
        'phone_otp_expires_at'   => 'datetime',
        'id_document_reviewed_at'=> 'datetime',
        'password'               => 'hashed',
    ];

    // ── KYC léger ──────────────────────────────────────────────────────────────
    public function isPhoneVerified(): bool { return !is_null($this->phone_verified_at); }
    public function isIdDocumentApproved(): bool { return $this->id_document_status === 'approved'; }

    /** Un utilisateur est considéré "KYC complet" quand pièce ET téléphone sont validés. */
    public function isFullyVerified(): bool
    {
        return $this->isPhoneVerified() && $this->isIdDocumentApproved();
    }

    // ── Relations ─────────────────────────────────────────────────────────────
    public function artisanProfile()
    {
        return $this->hasOne(ArtisanProfile::class);
    }

    public function ordersAsClient()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function ordersAsArtisan()
    {
        return $this->hasMany(Order::class, 'artisan_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'client_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'artisan_id');
    }

    public function deliveriesAsLivreur()
    {
        return $this->hasMany(Delivery::class, 'livreur_id');
    }

    // ── Helpers rôles ─────────────────────────────────────────────────────────
    public function isAdmin():   bool { return $this->role === 'admin'; }
    public function isArtisan(): bool { return $this->role === 'artisan'; }
    public function isClient():  bool { return $this->role === 'client'; }
    public function isLivreur(): bool { return $this->role === 'livreur'; }

    public function orderNotificationChannels(array $channels): array
    {
        if ($this->order_notifications === false) {
            return [];
        }

        if ($this->email_notifications === false) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        if (!$this->whatsapp_opt_in) {
            $channels = array_values(array_diff($channels, ['whatsapp']));
        }

        return $channels;
    }

    /** Langues disponibles pour la messagerie (traduction auto). */
    public static function availableLanguages(): array
    {
        return [
            'fr' => 'Français',
            'en' => 'English',
            'fon' => 'Fon',
            'yo'  => 'Yoruba',
            'ha'  => 'Hausa',
        ];
    }

    public function preferredLanguageLabel(): string
    {
        return self::availableLanguages()[$this->preferred_language] ?? $this->preferred_language;
    }

    public function avatarUrl(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=C4622D&color=fff';
    }

    /**
     * Slug SEO pour l'URL du profil (ex: "marie-koffi-poterie-cotonou").
     * On ne le stocke pas en base : l'URL canonique est {id}-{slug}, l'ID
     * fait foi pour la résolution, le slug n'est que décoratif/SEO. Ça évite
     * toute migration et toute gestion d'unicité, tout en gardant les liens
     * anciens ({id} seul) valides via redirection 301 vers l'URL canonique.
     */
    public function profileSlug(): string
    {
        $parts = array_filter([
            $this->name,
            $this->artisanProfile?->specialty,
            $this->city,
        ]);

        return \Illuminate\Support\Str::slug(implode('-', $parts)) ?: 'artisan';
    }

    /** URL canonique complète du profil, prête à l'emploi dans les vues (route('artisans.show', $user->routeSlug())). */
    public function routeSlug(): string
    {
        return $this->id . '-' . $this->profileSlug();
    }

    /**
     * Statistiques de réactivité de l'artisan aux messages clients.
     * Calculé automatiquement à partir de l'historique de messagerie,
     * aucune saisie manuelle : pour chaque commande où le client a écrit
     * en premier, on regarde si et en combien de temps l'artisan a répondu.
     *
     * @return array{rate: ?int, avg_hours: ?int, threads: int}
     */
    public function responseStats(): array
    {
        $orders = \App\Models\Order::where('artisan_id', $this->id)
            ->whereHas('messages')
            ->with(['messages' => fn($q) => $q->orderBy('created_at')])
            ->get();

        $threads   = 0;
        $responded = 0;
        $totalHours = 0;

        foreach ($orders as $order) {
            $messages = $order->messages;
            $first = $messages->first();
            if (! $first || $first->sender_id === $this->id) {
                continue; // pas de message, ou c'est l'artisan qui a initié
            }

            $threads++;
            $reply = $messages->first(fn($m) => $m->sender_id === $this->id && $m->created_at->gt($first->created_at));
            if ($reply) {
                $responded++;
                $totalHours += $first->created_at->diffInHours($reply->created_at);
            }
        }

        return [
            'threads'   => $threads,
            'rate'      => $threads > 0 ? (int) round(($responded / $threads) * 100) : null,
            'avg_hours' => $responded > 0 ? (int) round($totalHours / $responded) : null,
        ];
    }
}
