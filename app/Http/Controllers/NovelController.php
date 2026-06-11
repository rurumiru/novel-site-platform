<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\BetaReader;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\Banner;
use App\Models\NovelPromotion;
use App\Models\ReadingProgress;
use App\Models\NovelView;
use App\Models\Setting;
use App\Services\GeoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NovelController extends Controller {
    public function index(Request $request) {
        $isRu = GeoService::isRu($request->ip());
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();

        $layoutConfig = null;
        if (Auth::check()) {
            $userLayout = \App\Models\UserHomepageLayout::where('user_id', Auth::id())->first();
            if ($userLayout) $layoutConfig = $userLayout->layout;
        }
        if (!$layoutConfig) {
            $layoutConfig = json_decode(Setting::retrieve('homepage_layout', '[]'), true);
        }
        if (empty($layoutConfig)) {
            $layoutConfig = [
                ['type' => 'collection', 'title' => 'Популярное', 'sort' => 'views', 'period' => 'month', 'layout' => 'grid', 'rows' => 1],
                ['type' => 'updates', 'title' => 'Обновления'],
            ];
        }

        $blocks = [];
        foreach ($layoutConfig as $config) {
            if (($config['visible'] ?? true) === false) continue;

            $type = $config['type'] ?? 'collection';
            if ($type === 'block') $type = 'collection';

            if ($type === 'collection') {
                $isGuest = !Auth::check();
                $hideAdult = $isGuest && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
                $query = Novel::where('is_published', true)
                    ->when($isRu, fn($q) => $q->where('is_restricted', false))
                    ->when($hideAdult, fn($q) => $q->where('is_adult', false))
                    ->when($isGuest, fn($q) => $q->where('hide_from_guests', false));
                $sort = $config['sort'] ?? 'views';

                if ($sort === 'views') {
                    $period = $config['period'] ?? 'month';
                    if ($period === 'day') $query->withCount(['viewsLog' => fn($q) => $q->where('viewed_at', now()->toDateString())])->orderByDesc('views_log_count');
                    elseif ($period === 'month') $query->withCount(['viewsLog' => fn($q) => $q->where('viewed_at', '>=', now()->subDays(30)->toDateString())])->orderByDesc('views_log_count');
                    else $query->orderByDesc('views');
                } elseif ($sort === 'rating') {
                    $query->withCount(['ratings as avg_rating' => fn($q) => $q->select(DB::raw('coalesce(avg(score),0)'))])->orderByDesc('avg_rating');
                } else {
                    $query->latest();
                }

                $limit = ($config['rows'] ?? 1) * 5;
                $config['data'] = $query->withCount('chapters')->take($limit)->get();
                $config['type'] = 'block';

            } elseif ($type === 'updates') {
                $isGuestUpd = !Auth::check();
                $hideAdultGlobal = $isGuestUpd && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
                $config['data'] = Chapter::published()
                    ->with(['novel' => fn($q) => $q->select('id', 'title', 'cover_image', 'slug', 'is_adult', 'hide_from_guests')])
                    ->when($isGuestUpd, fn($q) => $q->whereHas('novel', fn($nq) => $nq->where('hide_from_guests', false)))
                    ->when($hideAdultGlobal, fn($q) => $q->whereHas('novel', fn($nq) => $nq->where('is_adult', false)))
                    ->select(['chapters.id', 'chapters.novel_id', 'chapters.title', 'chapters.sort_order', 'chapters.is_locked', 'chapters.is_published', 'chapters.published_at', 'chapters.created_at'])
                    ->orderByRaw('COALESCE(published_at, created_at) DESC')->take(10)->get();

            } elseif ($type === 'continue' && Auth::check()) {
                $config['data'] = ReadingProgress::where('user_id', Auth::id())
                    ->with(['novel' => fn($q) => $q->select('id', 'title', 'cover_image'), 'chapter:id,title,novel_id'])
                    ->latest('updated_at')->take(6)->get()->unique('novel_id')->values();
                if ($config['data']->isEmpty()) continue;

            } elseif ($type === 'favorites' && Auth::check()) {
                $favNovelIds = Auth::user()->favorites()->pluck('novels.id');
                if ($favNovelIds->isEmpty()) continue;
                $config['data'] = Chapter::published()
                    ->whereIn('novel_id', $favNovelIds)
                    ->with(['novel' => fn($q) => $q->select('id', 'title', 'cover_image')])
                    ->orderByRaw('COALESCE(published_at, created_at) DESC')->take(10)->get();
                if ($config['data']->isEmpty()) continue;

            } elseif ($type === 'top_rated') {
                $isGuestTR = !Auth::check();
                $hideAdultTR = $isGuestTR && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
                $config['data'] = Novel::where('is_published', true)
                    ->when($isRu, fn($q) => $q->where('is_restricted', false))
                    ->when($hideAdultTR, fn($q) => $q->where('is_adult', false))
                    ->when($isGuestTR, fn($q) => $q->where('hide_from_guests', false))
                    ->withCount(['ratings as avg_rating' => fn($q) => $q->select(DB::raw('coalesce(avg(score),0)'))])
                    ->whereHas('ratings')
                    ->orderByDesc('avg_rating')
                    ->withCount('chapters')->take(10)->get();
                $config['type'] = 'block';

            } elseif ($type === 'random') {
                $isGuestRnd = !Auth::check();
                $hideAdultRnd = $isGuestRnd && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
                $config['data'] = Novel::where('is_published', true)
                    ->when($isRu, fn($q) => $q->where('is_restricted', false))
                    ->when($hideAdultRnd, fn($q) => $q->where('is_adult', false))
                    ->when($isGuestRnd, fn($q) => $q->where('hide_from_guests', false))
                    ->inRandomOrder()->withCount('chapters')->take(10)->get();
                $config['type'] = 'block';

            } elseif ($type === 'schedule') {
                $config['data'] = Chapter::where('is_published', true)
                    ->whereNotNull('published_at')->where('published_at', '>', now())
                    ->with(['novel' => fn($q) => $q->select('id', 'title', 'cover_image')])
                    ->orderBy('published_at')->take(10)->get();
                if ($config['data']->isEmpty()) continue;

            } elseif ($type === 'banner_custom') {
            } else {
                continue;
            }

            $blocks[] = $config;
        }

        $featuredQuery = NovelPromotion::active()
            ->where('type', 'homepage_featured')
            ->with(['novel' => fn($q) => $q->where('is_published', true)->withCount('chapters')]);
        $featuredNovels = $featuredQuery->get()->pluck('novel')->filter();
        if (!Auth::check()) {
            $featuredNovels = $featuredNovels->where('hide_from_guests', false);
            if (filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN)) {
                $featuredNovels = $featuredNovels->where('is_adult', false);
            }
        }
        $featuredNovels = $featuredNovels->values();

        return view('welcome', compact('banners', 'blocks', 'featuredNovels'));
    }
    
    public function updates(Request $request) {
        $isRu = GeoService::isRu($request->ip());
        $chapters = Chapter::published()
            ->whereHas('novel', function($q) use ($isRu) { $q->where('is_published', true)->when($isRu, fn($sq) => $sq->where('is_restricted', false)); })
            ->with([
                'novel' => fn($q) => $q->withCount(['chapters' => fn($cq) => $cq->where('is_published', true)])->with('publisher:id,name'),
            ])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(30);
        return view('updates.index', compact('chapters'));
    }
    public function show(Request $request, $id) {
        $novel = Novel::with(['publisher', 'tags', 'genres'])->findOrFail($id);

        if (!Auth::check()) {
            if ($novel->hide_from_guests) abort(404);
            if ($novel->is_adult && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN)) abort(404);
        }

        $ip = $request->ip();
        $today = now()->toDateString();

        if (!NovelView::where('novel_id', $id)->where('ip_address', $ip)->where('viewed_at', $today)->exists()) {
            NovelView::create(['novel_id' => $id, 'ip_address' => $ip, 'user_agent' => $request->userAgent(), 'viewed_at' => $today]);
            $novel->increment('views');
        }

        $stats = [
            'day'   => NovelView::where('novel_id', $id)->where('viewed_at', $today)->count(),
            'month' => NovelView::where('novel_id', $id)->where('viewed_at', '>=', now()->subDays(30)->toDateString())->count(),
            'year'  => NovelView::where('novel_id', $id)->where('viewed_at', '>=', now()->subYear()->toDateString())->count(),
            'total' => $novel->views,
        ];

        $volumes = $novel->volumes()->with(['chapters' => fn($q) => $q->published()->orderBy('sort_order')])->orderBy('sort_order')->get();
        $chaptersWithoutVolume = $novel->chapters()->published()->whereNull('volume_id')->orderBy('sort_order')->get();

        $guestLimitEnabled = !Auth::check() && filter_var(Setting::retrieve('guest_chapter_limit_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $guestChapterLimit = (int) Setting::retrieve('guest_chapter_limit', 3);

        return view('novel.show', compact('novel', 'volumes', 'chaptersWithoutVolume', 'stats', 'guestLimitEnabled', 'guestChapterLimit'));
    }
    public function read($novel_id, $chapter_id) {
        $novel   = Novel::findOrFail($novel_id);
        $chapter = Chapter::where('novel_id', $novel_id)->findOrFail($chapter_id);

        if (!Auth::check()) {
            if ($novel->hide_from_guests) abort(404);
            if ($novel->is_adult && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN)) abort(404);
        }

        if (!Auth::check() && filter_var(Setting::retrieve('guest_chapter_limit_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $limit = (int) Setting::retrieve('guest_chapter_limit', 3);
            $chapterPosition = Chapter::where('novel_id', $novel_id)->published()->where('sort_order', '<=', $chapter->sort_order)->count();
            if ($chapterPosition > $limit) {
                return view('novel.guest-restricted', compact('novel', 'chapter'));
            }
        }

        $isBetaReader = Auth::check() && BetaReader::where('novel_id', $novel_id)->where('user_id', Auth::id())->exists();

        if (!$isBetaReader) {
            abort_if(!$novel->is_published || !$chapter->is_published, 404);
        }

        if (!$chapter->is_unlocked) return view('novel.locked', compact('novel', 'chapter'));

        if ($chapter->is_patron_advance) {
            $days = (int) ($chapter->patron_advance_days ?? 0);
            $age = $chapter->published_at ? now()->diffInDays($chapter->published_at) : 0;
            $expired = $days > 0 && $age >= $days;

            $hasAccess = false;
            if ($expired) {
                $hasAccess = true;
            } elseif (Auth::check()) {
                $u = Auth::user();
                $hasAccess = $u->canReadAdvance($novel_id)
                    || $u->hasRole(['super_admin', 'moderator', 'deputy_admin', 'owner'])
                    || ($novel->user_id === $u->id);
            }
            if (!$hasAccess) {
                return view('novel.locked', compact('novel', 'chapter'))->with('lockReason', 'patron');
            }
        }

        if (Auth::check()) ReadingProgress::updateOrCreate(
            ['user_id' => Auth::id(), 'novel_id' => $novel_id, 'chapter_id' => $chapter_id],
            ['is_completed' => true, 'updated_at' => now()]
        );

        $initialPercent = Auth::check()
            ? ReadingProgress::where('user_id', Auth::id())->where('novel_id', $novel_id)->where('chapter_id', $chapter_id)->value('percent')
            : null;

        if ($isBetaReader) {
            $volumes = $novel->volumes()->with(['chapters' => fn($q) => $q->orderBy('sort_order')])->orderBy('sort_order')->get();
            $chaptersWithoutVolume = $novel->chapters()->whereNull('volume_id')->orderBy('sort_order')->get();
        } else {
            $volumes = $novel->volumes()->with(['chapters' => fn($q) => $q->published()->orderBy('sort_order')])->orderBy('sort_order')->get();
            $chaptersWithoutVolume = $novel->chapters()->published()->whereNull('volume_id')->orderBy('sort_order')->get();
        }

        return view('novel.read', compact('novel', 'chapter', 'initialPercent', 'volumes', 'chaptersWithoutVolume', 'isBetaReader'));
    }

    public function chapterContent($novel_id, $chapter_id) {
        $novel = Novel::findOrFail($novel_id);
        $chapter = Chapter::where('novel_id', $novel_id)->findOrFail($chapter_id);
        abort_if(!$novel->is_published || !$chapter->is_published, 404);
        if (!$chapter->is_unlocked) return response()->json(['error' => 'locked'], 403);
        $html = view('novel.partials.chapter-content-block', [
            'chapter' => $chapter,
            'fontSize' => request('font_size', 20),
        ])->render();
        $next = $chapter->nextChapter();
        $prev = $chapter->prevChapter();
        return response()->json([
            'id' => $chapter->id,
            'title' => $chapter->title,
            'html' => $html,
            'next_id' => $next?->id,
            'prev_id' => $prev?->id,
            'comments_count' => $chapter->comments()->count(),
        ]);
    }
}
