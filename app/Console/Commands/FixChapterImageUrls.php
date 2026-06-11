<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FixChapterImageUrls extends Command
{
    protected $signature = 'chapters:fix-image-urls
                            {--dry-run : Показать что будет изменено без реального обновления}';

    protected $description = 'Заменяет локальные пути изображений на S3 URL в контенте глав';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $s3BaseUrl = rtrim(Storage::disk('s3')->url(''), '/');

        $this->info("S3 base URL: {$s3BaseUrl}");
        $this->info($dryRun ? '[DRY RUN] Поиск...' : 'Исправление URL изображений...');

        $chapters = DB::table('chapters')
            ->where('content', 'like', '%chapters_media/%')
            ->select('id', 'content')
            ->get();

        $this->info("Найдено глав для проверки: {$chapters->count()}");

        $fixed = 0;

        foreach ($chapters as $chapter) {
            $newContent = $chapter->content;

            $newContent = preg_replace_callback(
                '#(src=["\'])([^"\']+chapters_media/[^"\']+)(["\'])#',
                function ($m) use ($s3BaseUrl) {
                    $filename = $this->extractFilename($m[2]);
                    if (!$filename) return $m[0];
                    return $m[1] . $s3BaseUrl . '/chapters_media/' . $filename . $m[3];
                },
                $newContent
            );

            $newContent = preg_replace_callback(
                '#(\]\()([^)]*chapters_media/[^)]+)(\))#',
                function ($m) use ($s3BaseUrl) {
                    $filename = $this->extractFilename($m[2]);
                    if (!$filename) return $m[0];
                    return $m[1] . $s3BaseUrl . '/chapters_media/' . $filename . $m[3];
                },
                $newContent
            );

            if ($newContent !== $chapter->content) {
                $fixed++;
                if ($dryRun) {
                    $this->line("  Глава #{$chapter->id} — будет исправлена");
                } else {
                    DB::table('chapters')->where('id', $chapter->id)->update(['content' => $newContent]);
                    $this->line("  Глава #{$chapter->id} — исправлена");
                }
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "DRY RUN: будет исправлено {$fixed} глав. Запустите без --dry-run для применения."
            : "Исправлено {$fixed} глав.");

        return self::SUCCESS;
    }

    private function extractFilename(string $path): ?string
    {
        if (preg_match('#chapters_media/([A-Za-z0-9._-]+)$#', $path, $m)) {
            return $m[1];
        }
        return null;
    }
}
