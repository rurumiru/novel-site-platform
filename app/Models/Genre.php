<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Genre extends Model {
    protected $guarded = [];

    protected $casts = [
        'is_adult'   => 'boolean',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function novels(): BelongsToMany {
        return $this->belongsToMany(Novel::class, 'novel_genre');
    }

    protected static function booted(): void {
        static::saving(function (Genre $genre) {
            if (empty($genre->slug) && !empty($genre->name)) {
                $base = Str::slug($genre->name) ?: 'genre';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)
                    ->when($genre->exists, fn($q) => $q->where('id', '!=', $genre->id))
                    ->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $genre->slug = $slug;
            }
        });

        static::saved(function (Genre $genre) {
            if (!$genre->is_adult) return;

            $novelIds = $genre->novels()->pluck('novels.id');
            if ($novelIds->isEmpty()) return;

            Novel::whereIn('id', $novelIds)->update(['is_adult' => true]);
        });
    }
}
