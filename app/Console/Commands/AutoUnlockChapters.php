<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Models\Novel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoUnlockChapters extends Command
{
    protected $signature = 'app:auto-unlock-chapters';
    protected $description = 'Автоматически открывает следующую платную главу по таймеру';

    public function handle(): int
    {
        $novels = Novel::whereNotNull('auto_unlock_interval_days')
            ->where('auto_unlock_interval_days', '>=', 1)
            ->get();

        $unlocked = 0;

        foreach ($novels as $novel) {
            $lastAt = $novel->auto_unlock_last_at ? Carbon::parse($novel->auto_unlock_last_at) : null;

            if ($lastAt && $lastAt->addDays($novel->auto_unlock_interval_days)->isFuture()) {
                continue;
            }

            $nextLocked = Chapter::where('novel_id', $novel->id)
                ->where('is_published', true)
                ->where('is_locked', true)
                ->orderBy('sort_order', 'asc')
                ->first();

            if (!$nextLocked) {
                $novel->update([
                    'auto_unlock_interval_days' => null,
                    'auto_unlock_last_at' => null,
                ]);
                continue;
            }

            $nextLocked->update([
                'is_locked' => false,
                'price' => 0,
            ]);

            $novel->update(['auto_unlock_last_at' => now()]);
            $unlocked++;

            $this->info("Новелла [{$novel->id}] «{$novel->title}»: открыта глава «{$nextLocked->title}»");
        }

        $this->info("Готово. Открыто глав: {$unlocked}");
        return self::SUCCESS;
    }
}
