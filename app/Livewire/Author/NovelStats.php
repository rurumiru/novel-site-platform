<?php
namespace App\Livewire\Author;
use Livewire\Component;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\ChapterPurchase;
use App\Models\Donation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NovelStats extends Component {
    public Novel $novel;
    public string $period = '30';
    public string $selectedDate;

    protected $queryString = ['period'];

    public function mount($id) {
        $this->novel = Novel::where('id', $id)
            ->where(function($q) {
                $q->where('user_id', Auth::id())
                  ->orWhereHas('editors', fn($q2) => $q2->where('user_id', Auth::id()));
            })
            ->firstOrFail();

        $this->selectedDate = now()->format('Y-m-d');
    }

    public function setPeriod(string $p): void {
        $this->period = in_array($p, ['7','30','90','365','all'], true) ? $p : '30';
    }

    private function dateFloor(): ?Carbon {
        return $this->period === 'all' ? null : now()->subDays((int)$this->period);
    }

    private function timeSeries(string $table, string $dateColumn, $whereCallback = null): array {
        $floor = $this->dateFloor() ?? now()->subDays(30);
        $days = $this->period === 'all' ? 30 : (int)$this->period;

        $rows = DB::table($table)
            ->where($dateColumn, '>=', $floor)
            ->when($whereCallback, $whereCallback)
            ->select(DB::raw("DATE($dateColumn) as date"), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $series[] = ['date' => $d, 'count' => (int) ($rows[$d]->count ?? 0)];
        }
        return $series;
    }

    public function render() {
        $novelId = $this->novel->id;
        $chapterIds = $this->novel->chapters()->pluck('id');
        $publishedChapterIds = $this->novel->chapters()->where('is_published', true)->pluck('id');

        $totalViews = (int) DB::table('novel_views')->where('novel_id', $novelId)->count();
        $totalChapters = $this->novel->chapters()->count();
        $publishedCount = $publishedChapterIds->count();
        $draftCount = $totalChapters - $publishedCount;
        $lockedCount = $this->novel->chapters()->where('is_locked', true)->count();

        $totalLikes = $chapterIds->isEmpty() ? 0 : (int) DB::table('chapter_likes')->whereIn('chapter_id', $chapterIds)->count();
        $totalComments = (int) Comment::where('commentable_type', Novel::class)->where('commentable_id', $novelId)->count()
                       + ($chapterIds->isEmpty() ? 0 : (int) Comment::where('commentable_type', Chapter::class)->whereIn('commentable_id', $chapterIds)->count());
        $totalFavorites = (int) DB::table('favorites')->where('novel_id', $novelId)->count();
        $totalSubscribers = (int) Subscription::where('novel_id', $novelId)->where('status', 'active')->count();

        $ratingsCount = $this->novel->ratings()->count();
        $avgRating = round((float) $this->novel->ratings()->avg('score'), 2);
        $ratingDist = collect(range(1, 5))->mapWithKeys(function($s) use ($novelId) {
            return [$s => (int) DB::table('ratings')->where('novel_id', $novelId)->where('score', $s)->count()];
        });

        $unlockRevenue = 0;
        if ($chapterIds->isNotEmpty()) {
            $unlockRevenue = (int) DB::table('user_unlocked_chapters')
                ->join('chapters', 'user_unlocked_chapters.chapter_id', '=', 'chapters.id')
                ->whereIn('chapter_id', $chapterIds)
                ->sum('chapters.price');
        }
        $subRevenue = (int) Subscription::where('novel_id', $novelId)
            ->whereIn('status', ['active', 'expired', 'cancelled'])
            ->sum('amount_paid');
        $totalRevenue = $unlockRevenue + $subRevenue;

        $floor = $this->dateFloor();
        $periodViews = (int) DB::table('novel_views')->where('novel_id', $novelId)
            ->when($floor, fn($q) => $q->where('viewed_at', '>=', $floor->toDateString()))->count();
        $periodLikes = $chapterIds->isEmpty() ? 0 : (int) DB::table('chapter_likes')->whereIn('chapter_id', $chapterIds)
            ->when($floor, fn($q) => $q->where('created_at', '>=', $floor))->count();
        $periodFavs  = (int) DB::table('favorites')->where('novel_id', $novelId)
            ->when($floor, fn($q) => $q->where('created_at', '>=', $floor))->count();
        $periodSubs  = (int) Subscription::where('novel_id', $novelId)
            ->when($floor, fn($q) => $q->where('created_at', '>=', $floor))->count();

        $viewsSeries = $this->timeSeries('novel_views', 'viewed_at',
            fn($q) => $q->where('novel_id', $novelId));

        $likesSeries = $chapterIds->isEmpty() ? [] : $this->timeSeries('chapter_likes', 'created_at',
            fn($q) => $q->whereIn('chapter_id', $chapterIds));

        $commentsSeries = $this->timeSeries('comments', 'created_at',
            function($q) use ($novelId, $chapterIds) {
                return $q->where(function($qq) use ($novelId, $chapterIds) {
                    $qq->where(function($a) use ($novelId) {
                        $a->where('commentable_type', Novel::class)->where('commentable_id', $novelId);
                    });
                    if ($chapterIds->isNotEmpty()) {
                        $qq->orWhere(function($a) use ($chapterIds) {
                            $a->where('commentable_type', Chapter::class)->whereIn('commentable_id', $chapterIds);
                        });
                    }
                });
            });

        $favsSeries = $this->timeSeries('favorites', 'created_at',
            fn($q) => $q->where('novel_id', $novelId));

        $subsSeries = $this->timeSeries('subscriptions', 'created_at',
            fn($q) => $q->where('novel_id', $novelId));

        $latestChapters = Chapter::where('novel_id', $novelId)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take(10)->get(['id','title','sort_order','published_at','created_at','is_published']);

        $topChaptersByLikes = $chapterIds->isEmpty() ? collect() : Chapter::where('novel_id', $novelId)
            ->withCount('likes')
            ->orderByDesc('likes_count')->take(10)->get(['id','title','sort_order']);

        $topChaptersByComments = $chapterIds->isEmpty() ? collect() : Chapter::where('novel_id', $novelId)
            ->withCount('comments')
            ->orderByDesc('comments_count')->take(10)->get(['id','title','sort_order']);

        $revenueByChapter = $chapterIds->isEmpty() ? collect() : DB::table('user_unlocked_chapters')
            ->join('chapters', 'user_unlocked_chapters.chapter_id', '=', 'chapters.id')
            ->whereIn('chapter_id', $chapterIds)
            ->select('chapters.id', 'chapters.title', 'chapters.sort_order',
                DB::raw('count(*) as purchases'),
                DB::raw('sum(chapters.price) as revenue'))
            ->groupBy('chapters.id', 'chapters.title', 'chapters.sort_order')
            ->orderByDesc('revenue')
            ->take(10)->get();

        $retention = collect();
        if ($publishedChapterIds->isNotEmpty()) {
            $rows = DB::table('reading_progress')
                ->join('chapters', 'reading_progress.chapter_id', '=', 'chapters.id')
                ->where('chapters.novel_id', $novelId)
                ->select('chapters.sort_order', DB::raw('count(distinct reading_progress.user_id) as readers'))
                ->groupBy('chapters.sort_order')
                ->orderBy('chapters.sort_order')
                ->get();
            $retention = $rows;
        }

        $recentComments = collect();
        if ($publishedChapterIds->isNotEmpty() || true) {
            $recentComments = Comment::with(['user:id,name,avatar', 'commentable'])
                ->where(function($q) use ($novelId, $chapterIds) {
                    $q->where(function($a) use ($novelId) {
                        $a->where('commentable_type', Novel::class)->where('commentable_id', $novelId);
                    });
                    if ($chapterIds->isNotEmpty()) {
                        $q->orWhere(function($a) use ($chapterIds) {
                            $a->where('commentable_type', Chapter::class)->whereIn('commentable_id', $chapterIds);
                        });
                    }
                })
                ->latest()->take(8)->get();
        }

        $recentSubs = Subscription::with('user:id,name,avatar')
            ->where('novel_id', $novelId)
            ->latest()->take(8)->get();

        $donationsTotal = (int) Donation::where('novel_id', $novelId)
            ->where('status', 'completed')->sum('amount');
        $donationsCount = (int) Donation::where('novel_id', $novelId)
            ->where('status', 'completed')->count();
        $donationsPeriod = (int) Donation::where('novel_id', $novelId)
            ->where('status', 'completed')
            ->when($floor, fn($q) => $q->where('created_at', '>=', $floor))
            ->sum('amount');
        $topPatrons = Donation::where('novel_id', $novelId)
            ->where('status', 'completed')
            ->selectRaw('user_id, is_anonymous, SUM(amount) as total_amount, COUNT(*) as donations_count, MAX(created_at) as last_at')
            ->groupBy('user_id', 'is_anonymous')
            ->orderByDesc('total_amount')
            ->with('user:id,name,avatar')
            ->take(10)->get();
        $recentDonations = Donation::with('user:id,name,avatar')
            ->where('novel_id', $novelId)
            ->where('status', 'completed')
            ->latest()->take(8)->get();

        $compareFloor = $floor ? $floor->copy()->subDays((int)$this->period) : null;
        $prevPeriodViews = $compareFloor
            ? (int) DB::table('novel_views')->where('novel_id', $novelId)
                ->whereBetween('viewed_at', [$compareFloor->toDateString(), $floor->toDateString()])->count()
            : 0;
        $viewsTrend = $prevPeriodViews > 0 ? round(($periodViews - $prevPeriodViews) / $prevPeriodViews * 100) : null;

        return view('livewire.author.novel-stats', compact(
            'totalViews', 'totalChapters', 'publishedCount', 'draftCount', 'lockedCount',
            'totalLikes', 'totalComments', 'totalFavorites', 'totalSubscribers',
            'ratingsCount', 'avgRating', 'ratingDist',
            'unlockRevenue', 'subRevenue', 'totalRevenue',
            'periodViews', 'periodLikes', 'periodFavs', 'periodSubs', 'viewsTrend',
            'viewsSeries', 'likesSeries', 'commentsSeries', 'favsSeries', 'subsSeries',
            'latestChapters', 'topChaptersByLikes', 'topChaptersByComments',
            'revenueByChapter', 'retention', 'recentComments', 'recentSubs',
            'donationsTotal', 'donationsCount', 'donationsPeriod', 'topPatrons', 'recentDonations'
        ))->layout('layouts.app')->title('Статистика: ' . $this->novel->title);
    }
}
