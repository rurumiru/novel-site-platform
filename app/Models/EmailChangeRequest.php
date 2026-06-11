<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailChangeRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'code_sent_at'     => 'datetime',
        'code_verified_at' => 'datetime',
        'reviewed_at'      => 'datetime',
    ];

    public const STATUS_PENDING_CODE   = 'pending_code';
    public const STATUS_AWAITING_ADMIN = 'awaiting_admin';
    public const STATUS_APPROVED       = 'approved';
    public const STATUS_REJECTED       = 'rejected';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING_CODE, self::STATUS_AWAITING_ADMIN], true);
    }
}
