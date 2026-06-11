<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Chapter extends Model {
    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
        'is_locked' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $base = Str::slug($model->title);
                $suffix = '-' . uniqid();
                $maxBase = 240 - mb_strlen($suffix);
                if (mb_strlen($base) > $maxBase) $base = mb_substr($base, 0, $maxBase);
                $model->slug = $base . $suffix;
            }
        });
    }

    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function volume(): BelongsTo { return $this->belongsTo(Volume::class); }
    public function comments(): MorphMany { return $this->morphMany(Comment::class, 'commentable')->latest(); }
    public function likes() { return $this->belongsToMany(User::class, "chapter_likes"); }
    public function versions() { return $this->hasMany(ChapterVersion::class)->latest(); }
    
    public function scopePublished(Builder $query) {
        return $query->where('is_published', true)
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function nextChapter() {
        return Chapter::where('novel_id', $this->novel_id)->published()->where('sort_order', '>', $this->sort_order)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->first();
    }

    public function prevChapter() {
        return Chapter::where('novel_id', $this->novel_id)->published()->where('sort_order', '<', $this->sort_order)->orderBy('sort_order', 'desc')->orderBy('id', 'desc')->first();
    }

    public function getIsUnlockedAttribute(): bool {
        if (!$this->is_locked) return true;
        if (!Auth::check()) return false;
        $user = Auth::user();
        if ($user->hasRole('super_admin') || $user->id === $this->novel->user_id) return true;
        if (BetaReader::where('novel_id', $this->novel_id)->where('user_id', $user->id)->exists()) return true;
        $editorPivot = \DB::table('novel_editors')->where('novel_id', $this->novel_id)->where('user_id', $user->id)->first();
        if ($editorPivot && $editorPivot->can_read_paid) return true;
        if (!$user->hasVerifiedEmail()) return false;
        return $user->unlockedChapters()->where('chapter_id', $this->id)->exists();
    }
}
