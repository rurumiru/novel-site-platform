<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToS3 extends Command
{
    protected $signature = 'storage:migrate-to-s3
                            {--dry-run : Показать что будет скопировано без реального копирования}
                            {--force : Перезаписать уже существующие файлы в S3}';

    protected $description = 'Копирует все файлы из локального public-хранилища в S3 (локальные файлы не удаляются)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        $this->info($dryRun ? '[DRY RUN] Проверка файлов...' : 'Копирование файлов в S3...');

        $dirs = ['covers', 'backgrounds', 'avatars', 'banners', 'ads', 'blog', 'chapters_media', 'novels_media'];

        $total = 0;
        $copied = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($dirs as $dir) {
            $files = Storage::disk('public')->allFiles($dir);

            if (empty($files)) {
                $this->line("  <fg=gray>Пусто:</> {$dir}/");
                continue;
            }

            $this->line("  <fg=cyan>Папка:</> {$dir}/ (" . count($files) . " файлов)");

            foreach ($files as $file) {
                $total++;

                if (!$force && Storage::disk('s3')->exists($file)) {
                    $this->line("    <fg=yellow>Пропуск (уже есть):</> {$file}");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("    <fg=green>Будет скопирован:</> {$file}");
                    $copied++;
                    continue;
                }

                try {
                    $contents = Storage::disk('public')->get($file);
                    Storage::disk('s3')->put($file, $contents);
                    $this->line("    <fg=green>OK:</> {$file}");
                    $copied++;
                } catch (\Throwable $e) {
                    $this->error("    Ошибка: {$file} — " . $e->getMessage());
                    $errors++;
                }
            }
        }

        $this->newLine();
        $this->table(['', 'Кол-во'], [
            ['Всего файлов', $total],
            [$dryRun ? 'Будет скопировано' : 'Скопировано', $copied],
            ['Пропущено (уже есть)', $skipped],
            ['Ошибок', $errors],
        ]);

        if ($errors > 0) {
            $this->error('Завершено с ошибками.');
            return self::FAILURE;
        }

        $this->info($dryRun ? 'DRY RUN завершён. Запусти без --dry-run для реального копирования.' : 'Миграция завершена. Локальные файлы не тронуты.');
        return self::SUCCESS;
    }
}
