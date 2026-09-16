<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminExportLog extends Model
{
    protected $table = 'admin_export_logs';

    protected $fillable = [
        'user_id',
        'resource',
        'format',
        'filename',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
