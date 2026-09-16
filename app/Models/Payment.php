<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id', 'amount', 'commission', 'net_amount', 'commission_rate',
        'guarantee_contribution',
        'method', 'fedapay_transaction_id', 'status', 'paid_at',
        'reversed_at', 'reversed_by', 'reversal_note',
        'refund_requested_at', 'refund_requested_by', 'refund_note',
        'refunded_at', 'refunded_by', 'fedapay_payout_id', 'refund_status', 'refund_error',
    ];

    protected $casts = [
        'amount'                 => 'decimal:0',
        'commission'             => 'decimal:0',
        'net_amount'             => 'decimal:0',
        'guarantee_contribution' => 'decimal:0',
        'commission_rate'        => 'float',
        'paid_at'                => 'datetime',
        'reversed_at'            => 'datetime',
        'refund_requested_at'    => 'datetime',
        'refunded_at'            => 'datetime',
    ];

    // ── Paliers de commission progressifs ────────────────────────────────────
    //   0 –  50 000 XOF/mois  →  5%
    //  50 000 – 200 000 XOF/mois  →  8%
    //  + de 200 000 XOF/mois  → 10%

    const TIER_1_LIMIT = 50_000;   //  5%
    const TIER_2_LIMIT = 200_000;  //  8%
    //  au-delà               → 10%

    const TIER_1_RATE = 0.05;
    const TIER_2_RATE = 0.08;
    const TIER_3_RATE = 0.10;

    public function order()      { return $this->belongsTo(Order::class); }
    public function reversedBy() { return $this->belongsTo(User::class, 'reversed_by'); }
    public function refundRequestedBy() { return $this->belongsTo(User::class, 'refund_requested_by'); }
    public function refundedBy()        { return $this->belongsTo(User::class, 'refunded_by'); }

    /**
     * Calcule le taux de commission applicable à un artisan pour ce mois-ci.
     * On additionne tous ses paiements "completed" du mois en cours.
     */
    public static function getCommissionRate(int $artisanId): float
    {
        $monthlyTotal = self::whereHas('order', function ($q) use ($artisanId) {
                $q->where('artisan_id', $artisanId);
            })
            ->where('status', 'completed')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return match(true) {
            $monthlyTotal < self::TIER_1_LIMIT  => self::TIER_1_RATE,  // 5%
            $monthlyTotal < self::TIER_2_LIMIT  => self::TIER_2_RATE,  // 8%
            default                              => self::TIER_3_RATE,  // 10%
        };
    }

    /**
     * Calcule les montants (commission, net) pour une commande donnée.
     * Si artisanId est fourni, applique la commission progressive du mois
     * ET la contribution au fonds de garantie si l'artisan est au palier
     * Expert (seul palier habilité à offrir la garantie satisfait/repris).
     * Sinon, utilise le taux minimum (5%) par défaut, sans garantie.
     */
    public static function calculateAmounts(float $amount, ?int $artisanId = null): array
    {
        $rate       = $artisanId ? self::getCommissionRate($artisanId) : self::TIER_1_RATE;
        $commission = round($amount * $rate);
        $afterCommission = $amount - $commission;

        $guaranteeContribution = 0;
        if ($artisanId) {
            $profile = \App\Models\ArtisanProfile::where('user_id', $artisanId)->first();
            if ($profile && $profile->canOfferGuarantee()) {
                $guaranteeContribution = round($afterCommission * \App\Models\ArtisanProfile::GUARANTEE_CONTRIBUTION_RATE);
            }
        }

        return [
            'amount'                 => $amount,
            'commission_rate'        => $rate,
            'commission'             => $commission,
            'guarantee_contribution' => $guaranteeContribution,
            'net_amount'             => $afterCommission - $guaranteeContribution,
        ];
    }

    /**
     * Libellé lisible du taux appliqué (utile dans les vues).
     */
    public function commissionLabel(): string
    {
        return match($this->commission_rate) {
            self::TIER_1_RATE => '5% (Débutant)',
            self::TIER_2_RATE => '8% (Actif)',
            self::TIER_3_RATE => '10% (Top)',
            default           => round($this->commission_rate * 100) . '%',
        };
    }


    public function isReversed(): bool { return !is_null($this->reversed_at); }
    public function isPending():   bool { return $this->status === 'pending'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isRefunded():  bool { return $this->status === 'refunded'; }
    public function refundIsPending(): bool { return !is_null($this->refund_requested_at) && !$this->isRefunded(); }

    /**
     * Demande de remboursement : l'opération FedaPay est lancée par
     * l'administrateur depuis le flux sécurisé de remboursement.
     */
    public function markRefundRequested(User $admin, ?string $note = null): void
    {
        $this->update([
            'refund_requested_at' => now(),
            'refund_requested_by' => $admin->id,
            'refund_note'         => $note,
            'refund_status'       => 'requested',
            'refund_error'        => null,
        ]);
    }

    /** Confirme qu'un admin a bien effectué le remboursement côté FedaPay. */
    public function markRefunded(User $admin): void
    {
        $this->update([
            'status'      => 'refunded',
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
            'refund_status' => 'sent',
            'refund_error' => null,
        ]);
    }
}
