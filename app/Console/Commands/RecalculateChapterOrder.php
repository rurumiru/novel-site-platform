<?php

namespace App\Console\Commands;

use App\Models\Novel;
use Illuminate\Console\Command;

class RecalculateChapterOrder extends Command
{
    protected $signature = 'novels:recalculate-chapters';
    protected $description = 'Пересчитать sort_order глав по томам и дате публикации';

    public function handle(): int
    {
        $novels = Novel::all();
        foreach ($novels as $novel) {
            $novel->recalculateChapterSortOrders();
        }
        $this->info("Пересчитано глав для {$novels->count()} новелл.");
        return 0;
    }
}
