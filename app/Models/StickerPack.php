<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StickerPack extends Model {
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function stickers(): HasMany {
        return $this->hasMany(Sticker::class, 'pack_id')->orderBy('sort_order');
    }

    public function getCoverUrlAttribute(): ?string {
        return $this->cover_image ? Novel::storageUrl($this->cover_image) : null;
    }
}
