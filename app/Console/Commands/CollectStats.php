<?php
namespace App\Console\Commands;

use App\Models\Novel;
use App\Models\NovelView;
use App\Models\StatsSnapshot;
use App\Models\ChapterPurchase;
use App\Models\Comment;
use App\Models\Rating;
use App\Models\Chapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectStats extends Command {
    protected $signature = 'stats:collect';
    protected $description = 'Collect hourly stats snapshots for novels and authors';

    public function handle() {
        $now = now();
        $date = $now->toDateString();
        $hour = $now->hour;
        $hourStart = $now->copy()->startOfHour();
        $hourEnd = $now->copy()->endOfHour();

        $novels = Novel::where('is_published', true)->get();
        $bar = $this->output->createProgressBar($novels->count());

        foreach ($novels as $novel) {
            $views = NovelView::where('novel_id', $novel->id)->where('viewed_at', $date)->count();
            $uniqueVisitors = NovelView::where('novel_id', $novel->id)->where('viewed_at', $date)->distinct('ip_address')->count('ip_address');
            $favorites = $novel->favorites()->count();
            $comments = $novel->comments()->whereBetween('created_at', [$hourStart, $hourEnd])->count();
            $hasPurchases = \Schema::hasTable('chapter_purchases');
            $chapterPurchases = $hasPurchases ? DB::table('chapter_purchases')->where('novel_id', $novel->id)->whereBetween('created_at', [$hourStart, $hourEnd])->count() : 0;
            $revenue = $hasPurchases ? DB::table('chapter_purchases')->where('novel_id', $novel->id)->whereBetween('created_at', [$hourStart, $hourEnd])->sum('amount') : 0;
            $newChapters = Chapter::where('novel_id', $novel->id)->whereBetween('created_at', [$hourStart, $hourEnd])->count();
            $ratingsCount = $novel->ratings()->count();
            $ratingAvg = round($novel->ratings()->avg('score') ?? 0, 1);

            StatsSnapshot::updateOrCreate(
                ['type' => 'novel', 'entity_id' => $novel->id, 'date' => $date, 'hour' => $hour],
                [
                    'views' => $views,
                    'unique_visitors' => $uniqueVisitors,
                    'favorites' => $favorites,
                    'comments' => $comments,
                    'chapter_purchases' => $chapterPurchases,
                    'revenue' => $revenue,
                    'new_chapters' => $newChapters,
                    'ratings_count' => $ratingsCount,
                    'rating_avg' => $ratingAvg,
                ]
            );

            $bar->advance();
        }

        $authorIds = Novel::where('is_published', true)->distinct()->pluck('user_id');
        foreach ($authorIds as $authorId) {
            $novelIds = Novel::where('user_id', $authorId)->where('is_published', true)->pluck('id');
            $views = NovelView::whereIn('novel_id', $novelIds)->where('viewed_at', $date)->count();
            $unique = NovelView::whereIn('novel_id', $novelIds)->where('viewed_at', $date)->distinct('ip_address')->count('ip_address');
            $revenue = $hasPurchases ? DB::table('chapter_purchases')->whereIn('novel_id', $novelIds)->whereBetween('created_at', [$hourStart, $hourEnd])->sum('amount') : 0;

            StatsSnapshot::updateOrCreate(
                ['type' => 'author', 'entity_id' => $authorId, 'date' => $date, 'hour' => $hour],
                [
                    'views' => $views,
                    'unique_visitors' => $unique,
                    'revenue' => $revenue,
                    'favorites' => Novel::whereIn('id', $novelIds)->withCount('favorites')->get()->sum('favorites_count'),
                ]
            );
        }

        StatsSnapshot::updateOrCreate(
            ['type' => 'site', 'entity_id' => null, 'date' => $date, 'hour' => $hour],
            [
                'views' => NovelView::where('viewed_at', $date)->count(),
                'unique_visitors' => NovelView::where('viewed_at', $date)->distinct('ip_address')->count('ip_address'),
            ]
        );

        $bar->finish();
        $this->newLine();
        $this->info("Stats collected for {$date} hour {$hour}");
    }
}
