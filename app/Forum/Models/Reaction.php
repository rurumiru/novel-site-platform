<?php

namespace App\Forum\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    protected $table = 'forum_reactions';
    protected $guarded = [];

    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function reactable(): MorphTo { return $this->morphTo(); }

    public const ALLOWED = ['like', 'fire', 'heart', 'laugh', 'sad', 'angry'];
}
