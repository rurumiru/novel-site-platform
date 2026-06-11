<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NovelResource;
use App\Http\Resources\ChapterResource;
use App\Models\Novel;
use App\Models\Chapter;
use App\Services\GeoService;
use Illuminate\Http\Request;

class NovelApiController extends Controller
{
    public function index(Request $request)
    {
        $isRu = GeoService::isRu($request->ip());
        $query = Novel::where('is_published', true)
            ->when($isRu, fn($q) => $q->where('is_restricted', false));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        if ($tags = $request->input('tags')) {
            $ids = is_array($tags) ? $tags : explode(',', $tags);
            $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $ids));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'popular' => $query->orderByDesc('views'),
            'rating' => $query->withAvg('ratings', 'score')->orderByDesc('ratings_avg_score'),
            default => $query->latest(),
        };

        $perPage = min((int) $request->input('per_page', 12), 50);
        $novels = $query->withAvg('ratings', 'score')->withCount('chapters')->with('tags')->paginate($perPage)->withQueryString();

        return NovelResource::collection($novels);
    }

    public function show(Request $request, Novel $novel)
    {
        if (!$novel->is_published) {
            abort(404);
        }
        $novel->load(['tags', 'publisher:id,name,avatar', 'volumes.chapters' => fn($q) => $q->published()->orderBy('sort_order')]);
        $novel->loadCount('chapters');
        $novel->loadAvg('ratings', 'score');
        return new NovelResource($novel);
    }

    public function chapters(Novel $novel)
    {
        if (!$novel->is_published) {
            abort(404);
        }
        $chapters = $novel->chapters()->published()->orderBy('sort_order')
            ->select(['id', 'novel_id', 'title', 'slug', 'sort_order', 'published_at'])
            ->paginate(50);
        return ChapterResource::collection($chapters);
    }

    public function updates(Request $request)
    {
        $isRu = GeoService::isRu($request->ip());
        $chapters = Chapter::published()
            ->whereHas('novel', fn($q) => $q->where('is_published', true)->when($isRu, fn($sq) => $sq->where('is_restricted', false)))
            ->with(['novel:id,title,cover_image,slug'])
            ->latest('published_at')
            ->paginate($request->input('per_page', 30));

        return ChapterResource::collection($chapters);
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');
        if (mb_strlen($q) < 2) return response()->json(['novels' => [], 'authors' => []]);

        $novels = Novel::where('is_published', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('author_name', 'like', "%{$q}%");
            })
            ->withCount('chapters')
            ->take(10)->get(['id', 'title', 'cover_image', 'status', 'views']);

        $authors = \App\Models\User::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('username', 'like', "%{$q}%");
            })
            ->withCount(['novels' => fn($nq) => $nq->where('is_published', true)])
            ->take(5)->get(['id', 'name', 'username', 'avatar']);

        return response()->json([
            'novels' => $novels,
            'authors' => $authors,
        ]);
    }

    public function stats(Novel $novel)
    {
        if (!$novel->is_published) abort(404);
        $today = now()->toDateString();

        return response()->json([
            'views' => [
                'total' => $novel->views,
                'today' => $novel->viewsLog()->where('viewed_at', $today)->count(),
                'week'  => $novel->viewsLog()->where('viewed_at', '>=', now()->subWeek()->toDateString())->count(),
                'month' => $novel->viewsLog()->where('viewed_at', '>=', now()->subDays(30)->toDateString())->count(),
            ],
            'chapters' => $novel->publishedChapters()->count(),
            'favorites' => $novel->favorites()->count(),
            'rating' => [
                'average' => round($novel->ratings()->avg('score') ?? 0, 1),
                'count'   => $novel->ratings()->count(),
            ],
            'comments' => $novel->comments()->count(),
            'likes' => $novel->total_likes,
        ]);
    }
}
