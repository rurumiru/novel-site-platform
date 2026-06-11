<?php
namespace App\Livewire\Author;

use Livewire\Component;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\NovelView;
use App\Models\StatsSnapshot;
use App\Models\Comment;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Stats extends Component {
    public string $period = '30';
    public ?int $novelFilter = null;
    public bool $showCompare = false;

    public function setPeriod(string $p) { $this->period = $p; }
    public function setNovelFilter($id) { $this->novelFilter = $id ? (int) $id : null; }
    public function toggleCompare() { $this->showCompare = !$this->showCompare; }

    public function render() {
        $user = Auth::user();
        $allIds = $user->novels()->pluck('id')->merge($user->editedNovels()->pluck('novels.id'))->unique();
        $filterIds = $this->novelFilter ? collect([$this->novelFilter])->intersect($allIds) : $allIds;
        $days = (int) $this->period;
        $now = now();
        $dateFrom = $now->copy()->subDays($days)->toDateString();
        $datePrev = $now->copy()->subDays($days * 2)->toDateString();
        $today = $now->toDateString();
        $chapterIds = Chapter::whereIn('novel_id', $filterIds)->pluck('id');

        $totalViews = NovelView::whereIn('novel_id', $filterIds)->where('viewed_at', '>=', $dateFrom)->count();
        $prevViews = NovelView::whereIn('novel_id', $filterIds)->whereBetween('viewed_at', [$datePrev, $dateFrom])->count();
        $todayViews = NovelView::whereIn('novel_id', $filterIds)->where('viewed_at', $today)->count();
        $uniqueToday = NovelView::whereIn('novel_id', $filterIds)->where('viewed_at', $today)->distinct('ip_address')->count('ip_address');
        $totalFavorites = DB::table('favorites')->whereIn('novel_id', $filterIds)->count();
        $totalComments = Comment::where('commentable_type', Chapter::class)->whereIn('commentable_id', $chapterIds)->count();
        $newComments = Comment::where('commentable_type', Chapter::class)->whereIn('commentable_id', $chapterIds)->where('created_at', '>=', $dateFrom)->count();
        $totalChapters = Chapter::whereIn('novel_id', $filterIds)->where('is_published', true)->count();
        $newChapters = Chapter::whereIn('novel_id', $filterIds)->where('is_published', true)->where('created_at', '>=', $dateFrom)->count();
        $hasPurchases = Schema::hasTable('chapter_purchases');
        $revenue = $hasPurchases ? (float) (DB::table('chapter_purchases')->whereIn('novel_id', $filterIds)->where('created_at', '>=', $dateFrom)->sum('amount') ?? 0) : 0;
        $prevRevenue = $hasPurchases ? (float) (DB::table('chapter_purchases')->whereIn('novel_id', $filterIds)->whereBetween('created_at', [$datePrev, $dateFrom])->sum('amount') ?? 0) : 0;
        $totalLikes = DB::table('chapter_likes')->whereIn('chapter_id', $chapterIds)->count();
        $avgRating = round(Rating::whereIn('novel_id', $filterIds)->avg('score') ?? 0, 1);
        $ratingsCount = Rating::whereIn('novel_id', $filterIds)->count();
        $subsActive = DB::table('subscriptions')->whereIn('novel_id', $filterIds)->where('status', 'active')->count();

        $viewsByDay = NovelView::whereIn('novel_id', $filterIds)->where('viewed_at', '>=', $dateFrom)
            ->select('viewed_at', DB::raw('COUNT(*) as cnt'), DB::raw('COUNT(DISTINCT ip_address) as uniq'))
            ->groupBy('viewed_at')->orderBy('viewed_at')->get()->keyBy('viewed_at');

        $prevViewsByDay = $this->showCompare
            ? NovelView::whereIn('novel_id', $filterIds)->whereBetween('viewed_at', [$datePrev, $dateFrom])
                ->select('viewed_at', DB::raw('COUNT(*) as cnt'))->groupBy('viewed_at')->pluck('cnt', 'viewed_at')->toArray()
            : [];

        $commentsByDay = Comment::where('commentable_type', Chapter::class)->whereIn('commentable_id', $chapterIds)
            ->where('created_at', '>=', $dateFrom)->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->groupBy('d')->pluck('c', 'd')->toArray();

        $favsByDay = DB::table('favorites')->whereIn('novel_id', $filterIds)->where('created_at', '>=', $dateFrom)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->groupBy('d')->pluck('c', 'd')->toArray();

        $revByDay = $hasPurchases
            ? DB::table('chapter_purchases')->whereIn('novel_id', $filterIds)->where('created_at', '>=', $dateFrom)
                ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(amount) as t'))
                ->groupBy('d')->pluck('t', 'd')->toArray()
            : [];

        $labels = []; $dViews = []; $dUniq = []; $dPrev = []; $dComments = []; $dFavs = []; $dRev = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $dp = $now->copy()->subDays($i + $days)->toDateString();
            $labels[] = $now->copy()->subDays($i)->format('d.m');
            $dViews[] = $viewsByDay[$d]->cnt ?? 0;
            $dUniq[] = $viewsByDay[$d]->uniq ?? 0;
            $dPrev[] = $prevViewsByDay[$dp] ?? 0;
            $dComments[] = $commentsByDay[$d] ?? 0;
            $dFavs[] = $favsByDay[$d] ?? 0;
            $dRev[] = (float) ($revByDay[$d] ?? 0);
        }

        $driver = \DB::connection()->getDriverName();
        $dowExpr = $driver === 'pgsql'
            ? "EXTRACT(DOW FROM viewed_at)::int"
            : "DAYOFWEEK(viewed_at) - 1";
        $dowRaw = NovelView::whereIn('novel_id', $filterIds)
            ->where('viewed_at', '>=', $dateFrom)
            ->select(DB::raw($dowExpr . ' as d'), DB::raw('COUNT(*) as c'))
            ->groupBy('d')->pluck('c', 'd')->toArray();
        $dowMap = [1=>'ПН', 2=>'ВТ', 3=>'СР', 4=>'ЧТ', 5=>'ПТ', 6=>'СБ', 0=>'ВС'];
        $hLabels = []; $hData = [];
        foreach ($dowMap as $idx => $label) { $hLabels[] = $label; $hData[] = (int) ($dowRaw[$idx] ?? 0); }

        $rDist = Rating::whereIn('novel_id', $filterIds)->select('score', DB::raw('COUNT(*) as c'))
            ->groupBy('score')->orderBy('score')->pluck('c', 'score')->toArray();
        $ratingsDistData = []; for ($s = 1; $s <= 5; $s++) $ratingsDistData[] = $rDist[$s] ?? 0;

        $devRaw = NovelView::whereIn('novel_id', $filterIds)->where('viewed_at', '>=', $dateFrom)
            ->select('user_agent', DB::raw('COUNT(*) as c'))->groupBy('user_agent')->limit(1000)->get();
        $mobile = 0; $desktop = 0;
        foreach ($devRaw as $r) {
            if (preg_match('/Mobile|Android|iPhone|iPad/i', $r->user_agent ?? '')) $mobile += $r->c; else $desktop += $r->c;
        }

        $topChapters = Chapter::whereIn('novel_id', $filterIds)->where('is_published', true)
            ->withCount(['comments', 'likes'])->with('novel:id,title')
            ->orderByDesc('likes_count')->take(10)->get();

        $novelStats = Novel::whereIn('id', $filterIds)->withCount(['chapters' => fn($q) => $q->where('is_published', true)])
            ->get()->map(function ($n) use ($dateFrom, $chapterIds, $hasPurchases) {
                $nChIds = $n->chapters()->pluck('id');
                return [
                    'id' => $n->id, 'title' => $n->title, 'status' => $n->status,
                    'chapters_count' => $n->chapters_count,
                    'views' => NovelView::where('novel_id', $n->id)->where('viewed_at', '>=', $dateFrom)->count(),
                    'favorites' => $n->favorites()->count(),
                    'rating' => round($n->ratings()->avg('score') ?? 0, 1),
                    'comments' => Comment::where('commentable_type', Chapter::class)->whereIn('commentable_id', $nChIds)->count(),
                    'likes' => DB::table('chapter_likes')->whereIn('chapter_id', $nChIds)->count(),
                    'revenue' => $hasPurchases ? (float) (DB::table('chapter_purchases')->where('novel_id', $n->id)->where('created_at', '>=', $dateFrom)->sum('amount') ?? 0) : 0,
                ];
            })->sortByDesc('views')->values();

        $readersCount = DB::table('reading_progress')->whereIn('novel_id', $filterIds)->distinct('user_id')->count('user_id');
        $avgProgress = round(DB::table('reading_progress')->whereIn('novel_id', $filterIds)->avg('percent') ?? 0);

        return view('livewire.author.stats', compact(
            'totalViews', 'prevViews', 'todayViews', 'uniqueToday', 'totalFavorites',
            'totalComments', 'newComments', 'totalChapters', 'newChapters', 'revenue', 'prevRevenue',
            'totalLikes', 'avgRating', 'ratingsCount', 'subsActive', 'readersCount', 'avgProgress',
            'labels', 'dViews', 'dUniq', 'dPrev', 'dComments', 'dFavs', 'dRev',
            'hLabels', 'hData', 'ratingsDistData', 'mobile', 'desktop',
            'topChapters', 'novelStats'
        ) + ['novels' => Novel::whereIn('id', $allIds)->pluck('title', 'id')])
        ->layout('layouts.app')->title('Аналитика');
    }
}
