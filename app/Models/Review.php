<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Review extends Model {
    protected $guarded = [];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'is_published'     => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function recommends(): HasMany { return $this->hasMany(ReviewRecommend::class); }
    public function comments(): MorphMany { return $this->morphMany(Comment::class, 'commentable')->latest(); }

    public function scopePublished($q) { return $q->where('is_published', true); }
    public function scopePinned($q) { return $q->where('is_pinned', true); }
    public function scopeCategory($q, string $cat) { return $q->where('category', $cat); }

    public function getIsRecommendedAttribute(): bool {
        if (!Auth::check()) return false;
        return $this->recommends()->where('user_id', Auth::id())->exists();
    }

    public function getUrlAttribute(): string {
        return route('reviews.show', $this->id);
    }

    public function getCategoryLabelAttribute(): string {
        return match($this->category) {
            'notice'    => 'Уведомление',
            'promotion' => 'Продвижение',
            default     => 'Обзор работ',
        };
    }
}
