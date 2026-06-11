<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Banner extends Model {
    protected $guarded = [];

    public function getImageUrlAttribute(): ?string {
        return \App\Models\Novel::storageUrl($this->image_path);
    }

    public function getImageMobileUrlAttribute(): ?string {
        $path = $this->image_path_mobile ?: $this->image_path;
        return \App\Models\Novel::storageUrl($path);
    }
}
