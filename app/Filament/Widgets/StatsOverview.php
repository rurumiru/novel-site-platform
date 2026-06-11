<?php
namespace App\Filament\Widgets;

use App\Models\ChapterError;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Novel;
use App\Models\NovelView;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -3;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $usersTotal     = User::count();
        $novelsTotal    = Novel::where('is_published', true)->count();
        $chaptersTotal  = Chapter::where('is_published', true)->count();

        $today          = now()->toDateString();
        $weekStart      = now()->subDays(7)->toDateString();
        $monthStart     = now()->subDays(30)->toDateString();

        $newUsersWeek   = User::where('created_at', '>=', now()->subDays(7))->count();
        $newUsersToday  = User::whereDate('created_at', $today)->count();

        $viewsToday     = NovelView::where('viewed_at', $today)->count();
        $viewsWeek      = NovelView::where('viewed_at', '>=', $weekStart)->count();

        $commentsToday  = Comment::whereDate('created_at', $today)->count();
        $commentsWeek   = Comment::where('created_at', '>=', now()->subDays(7))->count();

        $revenueWeek = (int) Transaction::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('amount');
        $revenueToday = (int) Transaction::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $activeSubs = Schema::hasTable('subscriptions')
            ? Subscription::where('status', 'active')->count()
            : 0;
        $newSubsWeek = Schema::hasTable('subscriptions')
            ? Subscription::where('status', 'active')->where('created_at', '>=', now()->subDays(7))->count()
            : 0;

        return [
            Stat::make('Пользователи', number_format($usersTotal, 0, '.', ' '))
                ->description("+{$newUsersWeek} за неделю · {$newUsersToday} сегодня")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($newUsersWeek > 0 ? 'success' : 'gray'),

            Stat::make('Просмотры за неделю', $this->humanize($viewsWeek))
                ->description($this->humanize($viewsToday) . ' сегодня')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Доход за неделю', number_format($revenueWeek, 0, '.', ' ') . ' ₽')
                ->description(number_format($revenueToday, 0, '.', ' ') . ' ₽ сегодня')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($revenueWeek > 0 ? 'success' : 'gray'),

            Stat::make('Активные подписки', number_format($activeSubs, 0, '.', ' '))
                ->description("+{$newSubsWeek} новых за неделю")
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($activeSubs > 0 ? 'success' : 'gray'),

            Stat::make('Новеллы', number_format($novelsTotal, 0, '.', ' '))
                ->description($chaptersTotal . ' опубликованных глав')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('Комментарии за неделю', number_format($commentsWeek, 0, '.', ' '))
                ->description($commentsToday . ' сегодня')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($commentsToday > 0 ? 'success' : 'gray'),
        ];
    }

    private function humanize(int $n): string
    {
        if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000)     return round($n / 1_000, 1) . 'K';
        return (string) $n;
    }
}
