<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model {
    protected $guarded = [];
    protected $casts = [
        'is_anonymous' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
}
