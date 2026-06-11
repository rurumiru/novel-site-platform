<?php

namespace App\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $table = 'forum_tags';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) $m->slug = Str::slug($m->name);
        });
    }

    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'forum_thread_tag', 'tag_id', 'thread_id');
    }

    public function getRouteKeyName(): string { return 'slug'; }
}
