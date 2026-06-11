<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoCleanup extends Command
{
    protected $signature = 'demo:cleanup {--force : Запустить даже если DEMO_MODE выключен}';
    protected $description = 'Полностью сбрасывает демо-стенд: удаляет весь не-демо контент (is_demo=false) и всю пользовательскую активность.';

    public function handle(): int
    {
        if (!config('demo.enabled') && !$this->option('force')) {
            $this->warn('Демо-режим выключен (DEMO_MODE=false). Используйте --force, чтобы запустить принудительно.');
            return self::SUCCESS;
        }

        $totalWiped   = 0;
        $totalDeleted = 0;

        // 1. Полностью чистим таблицы пользовательской активности.
        foreach ((array) config('demo.wipe_tables', []) as $table) {
            $totalWiped += $this->purge("wipe:{$table}", function () use ($table) {
                if (!Schema::hasTable($table)) {
                    return 0;
                }
                return DB::table($table)->delete();
            });
        }

        // 2. В таблицах с эталоном удаляем всё, что не помечено как демо.
        foreach ((array) config('demo.flagged_tables', []) as $table) {
            $totalDeleted += $this->purge("flagged:{$table}", function () use ($table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'is_demo')) {
                    return 0;
                }
                return DB::table($table)->where('is_demo', false)->delete();
            });
        }

        // 3. Аккаунты посетителей (не-демо, без staff-ролей).
        $users = $this->purge('users', fn () => $this->purgeUsers());

        $this->info("Сброс демо-стенда завершён. Очищено активности: {$totalWiped}; удалено не-демо контента: {$totalDeleted}; удалено аккаунтов: {$users}.");
        return self::SUCCESS;
    }

    private function purge(string $label, callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            $this->error("Не удалось очистить «{$label}»: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Удаляет аккаунты, зарегистрированные посетителями: не-демо, без ролей,
     * кроме защищённого e-mail администратора. Аккаунты со staff-ролями не трогаются.
     */
    private function purgeUsers(): int
    {
        if (!Schema::hasColumn('users', 'is_demo')) {
            return 0;
        }

        $protectedEmail = config('demo.admin_email', 'admin@localhost');
        $protectedRoles = (array) config('demo.protected_roles', []);
        $hasRoles = Schema::hasTable('roles');

        $deleted = 0;
        User::where('is_demo', false)
            ->where('email', '!=', $protectedEmail)
            ->chunkById(200, function ($users) use (&$deleted, $hasRoles, $protectedRoles) {
                foreach ($users as $user) {
                    if ($hasRoles && $protectedRoles) {
                        try {
                            if ($user->roles()->whereIn('name', $protectedRoles)->exists()) {
                                continue;
                            }
                        } catch (\Throwable $e) {
                            continue;
                        }
                    }
                    try {
                        $user->delete();
                        $deleted++;
                    } catch (\Throwable $e) {
                        // FK-ограничения и т.п. — пропускаем такого пользователя
                    }
                }
            });

        return $deleted;
    }
}
