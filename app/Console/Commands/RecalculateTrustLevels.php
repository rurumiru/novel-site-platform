<?php
namespace App\Console\Commands;

use App\Services\TrustLevelService;
use Illuminate\Console\Command;

class RecalculateTrustLevels extends Command
{
    protected $signature = 'eriiba:recalc-trust';
    protected $description = 'Пересчитывает trust_level и patron_tier для всех пользователей.';

    public function handle(): int
    {
        $this->info('Пересчёт trust-уровней и patron-тиров…');
        $changed = TrustLevelService::recalculateAll();
        $this->info("Готово. Обновлено: {$changed} пользователей.");
        return self::SUCCESS;
    }
}
