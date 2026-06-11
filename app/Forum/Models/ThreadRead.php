<?php

namespace App\Forum\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadRead extends Model
{
    protected $table = 'forum_thread_reads';
    protected $guarded = [];
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;
    protected $casts = ['last_read_at' => 'datetime'];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function thread(): BelongsTo { return $this->belongsTo(Thread::class); }
}
