<?php
namespace App\Filament\Widgets;

use App\Models\Novel;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Widget;

class TopNovelsWidget extends Widget {
    protected static string $view = 'filament.widgets.top-novels';
    protected static ?int $sort = 11;
    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];

    protected function getViewData(): array {
        $weekAgo = now()->subDays(7)->toDateString();

        $novels = Novel::where('is_published', true)
            ->withCount(['viewsLog as views_week' => fn($q) => $q->where('viewed_at', '>=', $weekAgo)])
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(DB::raw('coalesce(avg(score),0)'))])
            ->withCount('chapters')
            ->orderByDesc('views_week')
            ->take(8)
            ->get();

        return ['novels' => $novels];
    }
}
