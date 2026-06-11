<?php

namespace App\Forum\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $table = 'forum_posts';
    protected $guarded = [];
    protected $casts = [
        'is_first'  => 'bool',
        'edited_at' => 'datetime',
    ];

    public function thread(): BelongsTo { return $this->belongsTo(Thread::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function editor(): BelongsTo { return $this->belongsTo(User::class, 'edited_by'); }

    public function parent(): BelongsTo { return $this->belongsTo(Post::class, 'parent_id'); }
    public function replies(): HasMany   { return $this->hasMany(Post::class, 'parent_id'); }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
