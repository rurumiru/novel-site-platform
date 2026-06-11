<?php

namespace App\Forum\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $table = 'forum_subscriptions';
    protected $guarded = [];
    public $incrementing = false;
    protected $primaryKey = null;
    protected $casts = ['notify' => 'bool'];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function thread(): BelongsTo { return $this->belongsTo(Thread::class); }
}
