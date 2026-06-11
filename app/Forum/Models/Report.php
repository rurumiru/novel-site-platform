<?php

namespace App\Forum\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $table = 'forum_reports';
    protected $guarded = [];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public const REASONS = ['spam', 'abuse', 'offtopic', 'nsfw', 'other'];

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
    public function reportable(): MorphTo { return $this->morphTo(); }
}
