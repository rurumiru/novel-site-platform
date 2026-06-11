<?php
namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\ChapterError;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget {
    protected static string $view = 'filament.widgets.recent-activity';
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];

    protected function getViewData(): array {
        $items = collect();

        User::latest()->take(5)->get()->each(function ($u) use ($items) {
            $items->push([
                'icon'  => 'fa-user-plus',
                'class' => 'accent',
                'text'  => 'Новый пользователь <strong>' . e($u->name) . '</strong>',
                'time'  => $u->created_at,
                'href'  => '/admin/users/' . $u->id . '/edit',
            ]);
        });

        Comment::with('user')->latest()->take(5)->get()->each(function ($c) use ($items) {
            $items->push([
                'icon'  => 'fa-comment',
                'class' => 'ok',
                'text'  => '<strong>' . e($c->user?->name ?? '—') . '</strong> комментарий',
                'time'  => $c->created_at,
                'href'  => '/admin/comments/' . $c->id . '/edit',
            ]);
        });

        Transaction::with('user')->latest()->take(5)->get()->each(function ($t) use ($items) {
            $items->push([
                'icon'  => 'fa-credit-card',
                'class' => $t->status === 'completed' ? 'ok' : ($t->status === 'pending' ? 'warn' : 'err'),
                'text'  => '<strong>' . e($t->user?->name ?? '—') . '</strong> ' . number_format($t->amount, 0) . ' ₽ · ' . e($t->status),
                'time'  => $t->created_at,
                'href'  => '/admin/transactions/' . $t->id . '/edit',
            ]);
        });

        ChapterError::with('user', 'chapter')->latest()->take(3)->get()->each(function ($e) use ($items) {
            $items->push([
                'icon'  => 'fa-flag',
                'class' => 'err',
                'text'  => '<strong>' . e($e->user?->name ?? '—') . '</strong> сообщил об ошибке',
                'time'  => $e->created_at,
                'href'  => '/admin/chapter-errors/' . $e->id . '/edit',
            ]);
        });

        $sorted = $items->sortByDesc(fn ($i) => $i['time'])->take(12)->values();

        return ['items' => $sorted];
    }
}
