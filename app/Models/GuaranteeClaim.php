<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuaranteeClaim extends Model
{
    protected $fillable = [
        'order_id', 'client_id', 'artisan_id',
        'reason', 'evidence_path', 'status',
        'admin_note', 'refund_amount', 'resolved_by', 'resolved_at',
        'refunded_at', 'refunded_by', 'fedapay_payout_id', 'refund_status', 'refund_error',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function order()      { return $this->belongsTo(Order::class); }
    public function client()     { return $this->belongsTo(User::class, 'client_id'); }
    public function artisan()    { return $this->belongsTo(User::class, 'artisan_id'); }
    public function resolvedBy() { return $this->belongsTo(User::class, 'resolved_by'); }
    public function refundedBy() { return $this->belongsTo(User::class, 'refunded_by'); }

    public function isPending():  bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    /** Approuvée mais pas encore confirmée comme remboursée par FedaPay. */
    public function refundIsPending(): bool { return $this->isApproved() && is_null($this->refunded_at); }

    /** Confirme qu'un admin a bien effectué le remboursement côté FedaPay. */
    public function markRefunded(User $admin): void
    {
        $this->update([
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
            'refund_status' => 'sent',
            'refund_error' => null,
        ]);
    }
}
