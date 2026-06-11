<?php

namespace App\Services;

use App\Models\Novel;
use App\Models\Volume;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class NovelZipImport
{
    private const TEXT_EXT = ['txt', 'md'];
    private const VOLUME_FOLDER_PATTERN = '#^(.*/)?(.+)/[^/]+$#';

    public function __construct(
        private Novel $novel
    ) {}

    public function importFromPath(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return ['volumes' => 0, 'chapters' => 0, 'errors' => ['Не удалось открыть ZIP файл.']];
        }

        $entries = $this->listTextEntries($zip);
        if (empty($entries)) {
            $zip->close();
            return ['volumes' => 0, 'chapters' => 0, 'errors' => ['В архиве нет подходящих файлов (.txt, .md).']];
        }

        $grouped = $this->groupByVolume($entries);
        $volumesCreated = 0;
        $chaptersCreated = 0;
        $errors = [];
        $sortOrder = 0;

        foreach ($grouped as $volumeName => $files) {
            $volume = null;
            if ($volumeName !== '') {
                $volume = $this->novel->volumes()->create([
                    'title' => $this->cleanTitle($volumeName),
                    'sort_order' => $volumesCreated + 1,
                ]);
                $volumesCreated++;
            }

            foreach ($files as $entry) {
                $content = $zip->getFromName($entry['path']);
                if ($content === false) {
                    $errors[] = 'Не удалось прочитать: ' . $entry['path'];
                    continue;
                }
                $content = $this->normalizeContent($content);
                $title = $this->chapterTitleFromFilename($entry['name']);
                if ($title === '') {
                    $title = 'Глава ' . ($chaptersCreated + 1);
                }
                $this->novel->chapters()->create([
                    'title' => $title,
                    'content' => $content,
                    'volume_id' => $volume?->id,
                    'sort_order' => ++$sortOrder,
                    'is_published' => true,
                ]);
                $chaptersCreated++;
            }
        }

        $zip->close();
        return ['volumes' => $volumesCreated, 'chapters' => $chaptersCreated, 'errors' => $errors];
    }

    public function importFromUploadedFile(UploadedFile $zipFile): array
    {
        return $this->importFromPath($zipFile->getRealPath());
    }

    private function listTextEntries(ZipArchive $zip): array
    {
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $path = $stat['name'];
            if (str_ends_with($path, '/')) {
                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, self::TEXT_EXT, true)) {
                continue;
            }
            $name = basename($path);
            $entries[] = ['path' => $path, 'name' => $name];
        }
        return $this->sortEntries($entries);
    }

    private function sortEntries(array $entries): array
    {
        usort($entries, function ($a, $b) {
            return strnatcasecmp($a['path'], $b['path']);
        });
        return $entries;
    }

    private function groupByVolume(array $entries): array
    {
        $byFolder = [];
        $hasFolders = false;
        foreach ($entries as $e) {
            $dir = dirname($e['path']);
            if ($dir !== '.' && $dir !== '') {
                $hasFolders = true;
            }
        }
        if (!$hasFolders) {
            return ['' => $entries];
        }
        foreach ($entries as $e) {
            $dir = dirname($e['path']);
            if ($dir === '.' || $dir === '') {
                $key = '';
            } else {
                $key = $dir;
            }
            $byFolder[$key][] = $e;
        }
        ksort($byFolder, SORT_NATURAL);
        return $byFolder;
    }

    private function normalizeContent(string $content): string
    {
        $content = trim($content);
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }
        return $content;
    }

    private function chapterTitleFromFilename(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = preg_replace('/^[\d\s\-\._]+\s*/', '', $base);
        return trim($base);
    }

    private function cleanTitle(string $title): string
    {
        return trim(preg_replace('/^[\d\s\-\._]+\s*/', '', $title)) ?: $title;
    }
}
