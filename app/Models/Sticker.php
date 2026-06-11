<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sticker extends Model {
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function pack(): BelongsTo {
        return $this->belongsTo(StickerPack::class, 'pack_id');
    }

    public function getUrlAttribute(): ?string {
        return $this->image_path ? Novel::storageUrl($this->image_path) : null;
    }
}
