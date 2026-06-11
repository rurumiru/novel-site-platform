<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model {
    protected $guarded = [];

    protected $casts = [
        'is_adult'      => 'boolean',
        'is_restricted' => 'boolean',
        'is_visible'    => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function novels(): BelongsToMany {
        return $this->belongsToMany(Novel::class, 'novel_tag');
    }

    protected static function booted(): void {
        static::saving(function (Tag $tag) {
            if (empty($tag->slug) && !empty($tag->name)) {
                $base = Str::slug($tag->name) ?: 'tag';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)
                    ->when($tag->exists, fn($q) => $q->where('id', '!=', $tag->id))
                    ->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $tag->slug = $slug;
            }
        });

        static::saved(function (Tag $tag) {
            if (!$tag->is_adult && !$tag->is_restricted) return;

            $novelIds = $tag->novels()->pluck('novels.id');
            if ($novelIds->isEmpty()) return;

            $update = [];
            if ($tag->is_adult) $update['is_adult'] = true;
            if ($tag->is_restricted) $update['is_restricted'] = true;
            if (!empty($update)) {
                Novel::whereIn('id', $novelIds)->update($update);
            }
        });
    }
}
