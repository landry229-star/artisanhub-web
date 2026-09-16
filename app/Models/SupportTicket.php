<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'number', 'user_id', 'email', 'subject', 'message',
        'status', 'priority', 'assigned_to', 'order_id', 'admin_reply',
        'resolved_at', 'sla_due_at', 'last_reply_at', 'dispute_reason',
        'internal_note',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)->orderBy('created_at');
    }

    public static function statusOptions(): array
    {
        return [
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'low' => 'Faible',
            'medium' => 'Moyenne',
            'high' => 'Élevée',
            'urgent' => 'Urgent',
        ];
    }
}
