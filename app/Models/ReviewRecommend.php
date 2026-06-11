<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewRecommend extends Model {
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = ['created_at' => 'datetime'];

    public function review(): BelongsTo { return $this->belongsTo(Review::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
