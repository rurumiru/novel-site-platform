<?php

namespace App\Forum\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActionLog extends Model
{
    protected $table = 'forum_action_logs';
    protected $guarded = [];
    protected $casts = ['meta' => 'array'];

    public function actor(): BelongsTo  { return $this->belongsTo(User::class, 'user_id'); }
    public function subject(): MorphTo  { return $this->morphTo(); }
}
