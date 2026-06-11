<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Novel extends Model {
    protected $guarded = [];
    protected $casts = ['is_adult' => 'boolean', 'hide_from_guests' => 'boolean', 'show_chapter_banner' => 'boolean', 'next_chapter_at' => 'datetime', 'auto_unlock_last_at' => 'datetime'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) $model->slug = Str::slug($model->title) . '-' . uniqid();
            if (Auth::check() && empty($model->user_id)) $model->user_id = Auth::id();
        });
    }

    public function publisher(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function editors(): BelongsToMany { return $this->belongsToMany(User::class, 'novel_editors', 'novel_id', 'user_id')->withPivot('can_read_paid')->withTimestamps(); }
    public function chapters(): HasMany { return $this->hasMany(Chapter::class)->orderBy('sort_order'); }
    public function publishedChapters(): HasMany { return $this->hasMany(Chapter::class)->published()->orderBy('sort_order'); }
    public function volumes(): HasMany { return $this->hasMany(Volume::class)->orderBy('sort_order'); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class); }
    public function genres(): BelongsToMany { return $this->belongsToMany(Genre::class, 'novel_genre'); }
    public function ratings(): HasMany { return $this->hasMany(Rating::class); }
    public function favorites() { return $this->belongsToMany(User::class, 'favorites'); }
    public function comments(): MorphMany { return $this->morphMany(Comment::class, 'commentable')->latest(); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function viewsLog(): HasMany { return $this->hasMany(NovelView::class); }
    public function teams(): BelongsToMany {
        return $this->belongsToMany(TranslationTeam::class, 'novel_team', 'novel_id', 'team_id')
            ->withPivot(['team_revenue_share', 'is_primary', 'show_credits'])
            ->withTimestamps();
    }
    public function primaryTeam(): ?TranslationTeam {
        return $this->teams()->wherePivot('is_primary', true)->first();
    }

    public function getRawRatingAttribute(): float {
        return round($this->ratings()->avg('score') ?? 0, 2);
    }

    public function getAverageRatingAttribute(): float {
        $v = (int) $this->ratings()->count();
        if ($v === 0) return 0.0;

        $R = (float) $this->ratings()->avg('score');

        [$globalAvg, $threshold] = self::ratingGlobals();

        $weighted = ($v * $R + $threshold * $globalAvg) / ($v + $threshold);
        return round($weighted, 1);
    }

    public static function ratingGlobals(): array {
        return \Illuminate\Support\Facades\Cache::remember('eri-rating-globals', 3600, function () {
            $globalAvg = (float) \DB::table('ratings')->avg('score');
            if ($globalAvg <= 0) $globalAvg = 4.0;
            $threshold = (int) \App\Models\Setting::retrieve('rating_threshold', 10);
            if ($threshold < 1) $threshold = 10;
            return [$globalAvg, $threshold];
        });
    }

    public static function storageUrl(?string $path): ?string {
        if (!$path || !trim($path)) return null;
        if (str_starts_with($path, 'http')) return $path;
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');
        $path = preg_replace('#^storage/#', '', $path);
        $base = config('filesystems.disks.s3.url');
        return $base ? rtrim($base, '/') . '/' . $path : '/storage/' . $path;
    }
    
    public function getTotalLikesAttribute() {
        return DB::table('chapter_likes')->whereIn('chapter_id', $this->chapters()->select('id'))->count();
    }

    public function getViewsPeriod($period) {
        $query = $this->viewsLog();
        switch ($period) {
            case 'day': $query->where('viewed_at', '>=', now()->startOfDay()); break;
            case 'week': $query->where('viewed_at', '>=', now()->subWeek()); break;
            case 'month': $query->where('viewed_at', '>=', now()->subMonth()); break;
            case 'year': $query->where('viewed_at', '>=', now()->subYear()); break;
            case 'decade': $query->where('viewed_at', '>=', now()->subYears(10)); break;
        }
        return $query->count();
    }

    public function getIsFavoritedAttribute(): bool {
        if (!Auth::check()) return false;
        return (bool) $this->favorites()->where('user_id', Auth::id())->exists();
    }

    public function getIsSubscribedAttribute(): bool {
        if (!Auth::check()) return false;
        if ($this->price <= 0) return true;
        if (Auth::user()->hasRole('super_admin') || Auth::id() === $this->user_id) return true;
        return $this->subscriptions()->where('user_id', Auth::id())->where('status', 'active')
            ->where(function($q) {
                $q->where('novel_id', $this->id)->orWhere(function($q2) {
                      $q2->where('type', 'author_bundle')->where('author_id', $this->user_id);
                  });
            })->exists();
    }

    public function getHasPendingSubscriptionAttribute(): bool {
        if (!Auth::check()) return false;
        return $this->subscriptions()->where('user_id', Auth::id())->where('status', 'pending')
            ->where(function($q) {
                $q->where('novel_id', $this->id)->orWhere(function($q2) {
                      $q2->where('type', 'author_bundle')->where('author_id', $this->user_id);
                  });
            })->exists();
    }
    
    public function getLastReadChapterAttribute() {
        if (!Auth::check()) return null;
        $progress = ReadingProgress::where('user_id', Auth::id())->where('novel_id', $this->id)->latest('updated_at')->first();
        return $progress ? Chapter::find($progress->chapter_id) : null;
    }
    
    public function getChapterCommentsAttribute() {
        return Comment::where('commentable_type', Chapter::class)
            ->whereIn('commentable_id', $this->chapters->pluck('id'))
            ->with(['user' => fn($q) => $q->select('id', 'name', 'avatar')], 'commentable')
            ->latest()
            ->limit(15)
            ->get();
    }

    public function getNextScheduledChapterAttribute() {
        return $this->chapters()->where('is_published', true)->whereNotNull('published_at')->where('published_at', '>', now())->orderBy('published_at', 'asc')->first();
    }

    public function getNextSortOrderForVolume(?int $volumeId, ?int $excludeChapterId = null): int {
        $volumes = $this->volumes()->orderBy('sort_order')->get();

        if ($volumeId === null || $volumeId === '') {
            $q = $this->chapters();
            if ($excludeChapterId) $q->where('id', '!=', $excludeChapterId);
            return ($q->max('sort_order') ?? 0) + 1;
        }

        $targetVolume = $volumes->firstWhere('id', (int) $volumeId);
        if (!$targetVolume) {
            $q = $this->chapters();
            if ($excludeChapterId) $q->where('id', '!=', $excludeChapterId);
            return ($q->max('sort_order') ?? 0) + 1;
        }

        $volumeSortOrder = $targetVolume->sort_order;
        $volIdsUpToTarget = $volumes->where('sort_order', '<=', $volumeSortOrder)->pluck('id');
        $q = $this->chapters()->whereIn('volume_id', $volIdsUpToTarget)->orderBy('sort_order', 'desc');
        if ($excludeChapterId) $q->where('id', '!=', $excludeChapterId);
        $lastInTargetRange = $q->first();

        return $lastInTargetRange ? $lastInTargetRange->sort_order + 1 : 1;
    }

    public function getFirstSortOrderForVolume(?int $volumeId): int {
        if ($volumeId === null || $volumeId === '') {
            $first = $this->chapters()->whereNull('volume_id')->orderBy('sort_order')->first();
            return $first ? (int) $first->sort_order : $this->getNextSortOrderForVolume(null);
        }

        $first = $this->chapters()->where('volume_id', (int) $volumeId)->orderBy('sort_order')->first();
        return $first ? (int) $first->sort_order : $this->getNextSortOrderForVolume((int) $volumeId);
    }

    public function shiftChaptersFrom(int $fromOrder): void {
        $this->chapters()->where('sort_order', '>=', $fromOrder)->increment('sort_order');
    }

    public function recalculateChapterSortOrders(): void {
        $volumes = $this->volumes()->orderBy('sort_order')->get();
        $order = 1;
        foreach ($volumes as $volume) {
            foreach ($volume->chapters()->orderBy('sort_order')->orderBy('id')->get() as $chapter) {
                $chapter->update(['sort_order' => $order++]);
            }
        }
        foreach ($this->chapters()->whereNull('volume_id')->orderBy('sort_order')->orderBy('id')->get() as $chapter) {
            $chapter->update(['sort_order' => $order++]);
        }
    }

    public function moveChapterToVolume(Chapter $chapter, ?int $volumeId): void {
        $oldOrder = $chapter->sort_order;
        $newOrder = $this->getNextSortOrderForVolume($volumeId, $chapter->id);

        if ($chapter->volume_id == $volumeId && $newOrder == $oldOrder) {
            return;
        }

        $chapter->update(['volume_id' => $volumeId ?: null]);
        $chapter->update(['sort_order' => -1]);

        $this->chapters()->where('id', '!=', $chapter->id)->where('sort_order', '>', $oldOrder)->decrement('sort_order');

        $newOrder = $this->getNextSortOrderForVolume($volumeId, $chapter->id);
        $this->shiftChaptersFrom($newOrder);
        $chapter->update(['sort_order' => $newOrder]);
    }
}
