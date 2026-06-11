<?php
namespace App\Filament\Widgets;

use App\Models\ChapterError;
use App\Models\EmailChangeRequest;
use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class ApprovalQueuesWidget extends Widget
{
    protected static string $view = 'filament.widgets.approval-queues';
    protected static ?int $sort = -2;
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $queues = [];

        if (Schema::hasTable('subscriptions')) {
            $queues[] = [
                'label' => 'Подписки',
                'icon'  => 'fa-crown',
                'count' => Subscription::where('status', 'pending')->count(),
                'href'  => url('/admin/subscriptions?tableFilters[status][value]=pending'),
            ];
        }

        $queues[] = [
            'label' => 'Жалобы / ошибки',
            'icon'  => 'fa-flag',
            'count' => ChapterError::where('status', 'new')->count(),
            'href'  => url('/admin/chapter-errors?tableFilters[status][value]=new'),
        ];

        if (Schema::hasColumn('users', 'bio_pending')) {
            $bioCount = User::whereNotNull('bio_pending')->where('bio_pending', '!=', '')->count();
            $queues[] = [
                'label' => 'Био на проверку',
                'icon'  => 'fa-user-pen',
                'count' => $bioCount,
                'href'  => url('/admin/users?tableFilters[bio_pending][isActive]=true'),
            ];
        }

        if (Schema::hasTable('email_change_requests')) {
            $emailChanges = EmailChangeRequest::where('status', 'awaiting_admin')->count();
            $queues[] = [
                'label' => 'Смена email',
                'icon'  => 'fa-envelope',
                'count' => $emailChanges,
                'href'  => url('/admin/email-change-requests'),
            ];
        }

        return [
            'queues' => $queues,
            'totalPending' => array_sum(array_column($queues, 'count')),
        ];
    }
}
