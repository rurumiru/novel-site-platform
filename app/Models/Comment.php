<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Comment extends Model {
    protected $guarded = [];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function commentable(): MorphTo { return $this->morphTo(); }
    
    public function replies(): HasMany { return $this->hasMany(Comment::class, 'parent_id')->with('user')->latest(); }
    public function parent(): BelongsTo { return $this->belongsTo(Comment::class, 'parent_id'); }

    public function likes(): HasMany { return $this->hasMany(CommentLike::class); }

    public function getIsLikedAttribute(): bool {
        if (!Auth::check()) return false;
        return $this->likes()->where('user_id', Auth::id())->exists();
    }

    public function recommendations(): HasMany { return $this->hasMany(CommentRecommendation::class); }

    public function getIsRecommendedAttribute(): bool {
        if (!Auth::check()) return false;
        return $this->recommendations()->where('user_id', Auth::id())->exists();
    }

    public function reports(): HasMany { return $this->hasMany(CommentReport::class); }
}
