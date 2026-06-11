<?php

namespace App\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Section extends Model
{
    protected $table = 'forum_sections';
    protected $guarded = [];
    protected $casts = ['is_active' => 'bool'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) $m->slug = Str::slug($m->name);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class)->orderByDesc('is_pinned')->orderByDesc('last_post_at');
    }

    public function publicThreads(): HasMany
    {
        return $this->threads()->where('visibility', 'public');
    }

    public function getRouteKeyName(): string { return 'slug'; }
}
