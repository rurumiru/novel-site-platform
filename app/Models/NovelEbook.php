<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovelEbook extends Model {
    public $timestamps = false;
    protected $fillable = ['novel_id', 'format', 'access_type', 'file_path', 'chapters_hash', 'file_size'];

    public function novel() {
        return $this->belongsTo(Novel::class);
    }

    public function scopeForFormat($query, string $format) {
        return $query->where('format', $format);
    }

    public function scopeForAccess($query, string $accessType) {
        return $query->where('access_type', $accessType);
    }
}
