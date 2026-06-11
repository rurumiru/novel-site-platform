<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model {
    protected $table = 'reading_progress';
    protected $guarded = [];

    public function novel(): BelongsTo {
        return $this->belongsTo(Novel::class);
    }

    public function chapter(): BelongsTo {
        return $this->belongsTo(Chapter::class);
    }
}
