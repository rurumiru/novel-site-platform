<?php
namespace App\Filament\Widgets;

use App\Models\NovelView;
use Filament\Widgets\ChartWidget;

class TrafficChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Просмотры за 30 дней';
    protected static ?string $description = 'Открытия страниц новелл и глав по дням';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        $rows = NovelView::where('viewed_at', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('viewed_at as d, COUNT(*) as total')
            ->groupBy('viewed_at')
            ->pluck('total', 'd');

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d.m');
            $values[] = (int) ($rows[$date] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Просмотры',
                    'data' => $values,
                    'borderColor' => '#34d399',
                    'backgroundColor' => 'rgba(52, 211, 153, 0.12)',
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
