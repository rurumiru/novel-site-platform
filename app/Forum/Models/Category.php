<?php

namespace App\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $table = 'forum_categories';
    protected $guarded = [];
    protected $casts = ['is_active' => 'bool'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) $m->slug = Str::slug($m->name);
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }

    public function getRouteKeyName(): string { return 'slug'; }
}
