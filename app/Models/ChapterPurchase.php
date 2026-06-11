<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterPurchase extends Model {
    protected $table = 'user_unlocked_chapters';
    protected $guarded = [];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function chapter(): BelongsTo { return $this->belongsTo(Chapter::class); }
}
