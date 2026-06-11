<?php
namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Доход за 30 дней';
    protected static ?string $description = 'Сумма успешных транзакций по дням, ₽';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        $rows = Transaction::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d.m');
            $values[] = (int) ($rows[$date] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Доход, ₽',
                    'data' => $values,
                    'borderColor' => '#5d90fa',
                    'backgroundColor' => 'rgba(93, 144, 250, 0.12)',
                    'tension' => 0.35,
                    'fill' => true,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'grid' => ['color' => 'rgba(125, 138, 160, 0.1)'],
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['maxTicksLimit' => 10],
                ],
            ],
            'interaction' => ['intersect' => false, 'mode' => 'index'],
        ];
    }
}
