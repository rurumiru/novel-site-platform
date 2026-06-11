<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Volume extends Model {
    protected $guarded = [];

    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function chapters(): HasMany { return $this->hasMany(Chapter::class)->orderBy('sort_order'); }
}
