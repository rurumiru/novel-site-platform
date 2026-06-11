<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AdBanner extends Model {
    protected $guarded = [];

    public function getImageUrlAttribute(): ?string {
        return \App\Models\Novel::storageUrl($this->image);
    }
}
