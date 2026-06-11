<?php
namespace App\Filament\Pages;

use App\Models\ChapterPurchase;
use App\Models\Subscription;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class PurchaseLog extends Page {
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';

    public static function canAccess(): bool {
        return Schema::hasTable('chapter_purchases');
    }
    protected static ?string $navigationLabel = 'Журнал продаж';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 0;
    protected static string  $view = 'filament.pages.purchase-log';

    public function getViewData(): array {
        return [
            'chapterRevenue' => ChapterPurchase::where('price_paid', '>', 0)->sum('price_paid'),
            'chapterCount'   => ChapterPurchase::where('price_paid', '>', 0)->count(),
            'subsRevenue'    => Subscription::whereIn('status', ['active'])->sum('amount_paid'),
            'subsCount'      => Subscription::where('status', 'active')->count(),
            'pendingCount'   => Subscription::where('status', 'pending')->count(),
            'recentChapters' => ChapterPurchase::with([
                                    'user:id,name',
                                    'chapter:id,title,novel_id',
                                    'chapter.novel:id,title',
                                ])
                                ->where('price_paid', '>', 0)
                                ->orderByDesc('created_at')
                                ->limit(20)
                                ->get(),
            'recentSubs'     => Subscription::with(['user:id,name', 'novel:id,title'])
                                ->orderByDesc('created_at')
                                ->limit(20)
                                ->get(),
        ];
    }
}
