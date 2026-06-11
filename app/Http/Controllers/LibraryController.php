<?php
namespace App\Http\Controllers;

use App\Models\BetaReader;
use App\Models\Novel;
use App\Models\ReadingProgress;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller {

    public function index() {
        $user = Auth::user();

        $favorites = $user->favorites()
            ->where('is_published', true)
            ->withCount('chapters')
            ->with('genres:id,name')
            ->latest('favorites.created_at')
            ->get();

        $progressByNovel = ReadingProgress::where('user_id', $user->id)
            ->with([
                'novel' => fn($q) => $q->withCount('chapters')->with('genres:id,name'),
                'chapter:id,title,novel_id,sort_order',
            ])
            ->orderByDesc('updated_at')
            ->get()
            ->filter(fn($p) => $p->novel)
            ->unique('novel_id')
            ->values();

        $bookmarks = collect();
        try {
            $bookmarks = \App\Models\ChapterBookmark::where('user_id', $user->id)
                ->with([
                    'chapter:id,title,novel_id,sort_order,volume_id',
                    'chapter.novel:id,title,cover_image,user_id',
                    'chapter.volume:id,title',
                ])
                ->orderByDesc('created_at')
                ->get()
                ->filter(fn($b) => $b->chapter && $b->chapter->novel);
        } catch (\Throwable $e) {
        }

        $reading  = collect();
        $finished = collect();
        $paused   = collect();

        $now = now();
        foreach ($progressByNovel as $p) {
            $totalCh = $p->novel->chapters_count ?? 0;
            $curOrder = $p->chapter?->sort_order ?? 0;
            $percent  = $totalCh > 0 ? min(100, (int) round(($curOrder / $totalCh) * 100)) : 0;
            $p->lib_percent = $percent;
            $p->lib_chapter = $curOrder;

            $daysSince = $p->updated_at ? $p->updated_at->diffInDays($now) : 0;

            if ($percent >= 100 || $p->is_completed) {
                $finished->push($p);
            } elseif ($daysSince > 30) {
                $paused->push($p);
            } else {
                $reading->push($p);
            }
        }

        $betaNovels = Novel::whereIn('id', BetaReader::where('user_id', $user->id)->pluck('novel_id'))
            ->withCount('chapters')
            ->with('genres:id,name')
            ->get();

        return view('library.index', compact(
            'user', 'favorites', 'reading', 'finished', 'paused', 'betaNovels', 'bookmarks'
        ));
    }
}
