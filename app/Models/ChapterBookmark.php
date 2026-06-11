<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterBookmark extends Model {
    protected $guarded = [];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function chapter(): BelongsTo { return $this->belongsTo(Chapter::class); }
}
