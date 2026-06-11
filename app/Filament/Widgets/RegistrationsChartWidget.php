<?php
namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class RegistrationsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Регистрации за 30 дней';
    protected static ?string $description = 'Новые пользователи по дням';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        $rows = User::where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
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
                    'label' => 'Регистрации',
                    'data' => $values,
                    'backgroundColor' => '#a78bfa',
                    'borderColor' => '#8b5cf6',
                    'borderRadius' => 4,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
