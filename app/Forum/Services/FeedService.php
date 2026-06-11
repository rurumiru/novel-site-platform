<?php

namespace App\Forum\Services;

use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FeedService
{
    public const CACHE_TTL_SECONDS = 20;

    private static ?string $coverColumn = null;

    private static function coverColumn(): string
    {
        if (self::$coverColumn !== null) return self::$coverColumn;
        try {
            $cols = Schema::getColumnListing('novels');
        } catch (\Throwable) {
            return self::$coverColumn = 'cover_image';
        }
        foreach (['cover_image', 'cover', 'image', 'thumbnail'] as $c) {
            if (in_array($c, $cols, true)) return self::$coverColumn = $c;
        }
        return self::$coverColumn = '';
    }

    private function withCover(array $base): array
    {
        $c = self::coverColumn();
        return $c === '' ? $base : array_merge($base, [$c]);
    }

    private function coverOf(?Novel $n): ?string
    {
        $c = self::coverColumn();
        if (!$n || $c === '') return null;
        $val = $n->{$c} ?? null;
        return $val ? Novel::storageUrl($val) : null;
    }

    public function snapshot(int $limit = 8): array
    {
        return Cache::remember('forum:feed:snapshot:' . $limit, self::CACHE_TTL_SECONDS, function () use ($limit) {
            return [
                'updated_at'      => now()->toIso8601String(),
                'new_novels'      => $this->newNovels($limit),
                'top_novels'      => $this->topNovels($limit),
                'latest_chapters' => $this->latestChapters($limit),
            ];
        });
    }

    public function newNovels(int $limit): array
    {
        return Novel::query()
            ->where(function ($q) {
                $q->whereNull('hide_from_guests')->orWhere('hide_from_guests', false);
            })
            ->latest('created_at')
            ->limit($limit)
            ->get($this->withCover(['id', 'title', 'slug', 'created_at', 'user_id']))
            ->map(fn (Novel $n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'url'        => route('novel.show', ['id' => $n->id]),
                'cover'      => $this->coverOf($n),
                'created_at' => optional($n->created_at)->toIso8601String(),
                'author'     => optional($n->publisher)->name,
            ])
            ->all();
    }

    public function topNovels(int $limit): array
    {
        $cols         = $this->safeColumns('novels');
        $hasRatingAvg = in_array('rating_avg', $cols, true);

        $q = Novel::query()
            ->where(function ($q) {
                $q->whereNull('hide_from_guests')->orWhere('hide_from_guests', false);
            });

        if ($hasRatingAvg) {
            $q->orderByDesc('rating_avg')->orderByDesc('id');
        } else {
            $q->withCount('favorites')->orderByDesc('favorites_count')->orderByDesc('id');
        }

        return $q->limit($limit)->get($this->withCover(['id', 'title', 'slug']))
            ->map(fn (Novel $n) => [
                'id'     => $n->id,
                'title'  => $n->title,
                'url'    => route('novel.show', ['id' => $n->id]),
                'cover'  => $this->coverOf($n),
                'rating' => method_exists($n, 'getAverageRatingAttribute') ? $n->average_rating : null,
            ])
            ->all();
    }

    private function safeColumns(string $table): array
    {
        try { return Schema::getColumnListing($table); }
        catch (\Throwable) { return []; }
    }

    public function latestChapters(int $limit): array
    {
        return Chapter::query()
            ->with(['novel'])
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->whereHas('novel', function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('hide_from_guests')->orWhere('hide_from_guests', false);
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'novel_id', 'title', 'slug', 'published_at'])
            ->map(function (Chapter $c) {
                return [
                    'id'           => $c->id,
                    'title'        => $c->title,
                    'novel_title'  => optional($c->novel)->title,
                    'novel_url'    => $c->novel_id ? route('novel.show', ['id' => $c->novel_id]) : null,
                    'cover'        => $this->coverOf($c->novel),
                    'url'          => $c->novel_id
                        ? route('novel.read', ['novel_id' => $c->novel_id, 'chapter_id' => $c->id])
                        : '#',
                    'published_at' => optional($c->published_at)->toIso8601String(),
                ];
            })
            ->all();
    }
}
