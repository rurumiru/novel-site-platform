<?php
namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Setting;
use App\Services\EbookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DownloadController extends Controller {
    protected $ebookService;

    public function __construct(EbookService $ebookService) {
        $this->ebookService = $ebookService;
    }

    public function download(Request $request, $id, $format) {
        if (!filter_var(Setting::retrieve('downloads_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            abort(403, 'Скачивание файлов отключено администратором.');
        }

        $requireAuth = filter_var(Setting::retrieve('downloads_require_auth', '1'), FILTER_VALIDATE_BOOLEAN);
        if ($requireAuth && !Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array($format, ['txt', 'fb2', 'epub'])) abort(404);
        $allowedFormats = json_decode(Setting::retrieve('download_formats', '["txt","fb2","epub"]'), true)
                          ?? ['txt', 'fb2', 'epub'];
        if (!in_array($format, $allowedFormats)) {
            abort(403, 'Этот формат скачивания отключён администратором.');
        }

        $novel = Novel::with(['publisher', 'chapters' => fn($q) => $q->orderBy('sort_order')])
                      ->findOrFail($id);

        $isAdmin = Auth::check() && (Auth::user()->hasRole('super_admin') || Auth::id() === $novel->user_id);

        $rawVolumes = $request->input('volumes');
        $volumeIds = null;
        if (is_array($rawVolumes) && count($rawVolumes) > 0) {
            $volumeIds = array_values(array_filter(array_map('intval', $rawVolumes), fn($v) => $v > 0));
        }
        $includeNoVolume = $request->boolean('novolume');

        $chapters = $novel->chapters->filter(function ($chapter) use ($isAdmin, $volumeIds, $includeNoVolume) {
            if (!$chapter->is_published) return false;
            if ($chapter->published_at && $chapter->published_at->isFuture() && !$isAdmin) return false;
            if ($chapter->is_locked) return false;
            if ($volumeIds !== null || $includeNoVolume) {
                if ($chapter->volume_id === null) return $includeNoVolume;
                return $volumeIds !== null && in_array((int) $chapter->volume_id, $volumeIds, true);
            }
            return true;
        });

        if ($chapters->isEmpty()) {
            return back()->with('error', 'Для выбранных томов нет свободных глав для скачивания.');
        }

        $novel->setRelation('chapters', $chapters);

        try {
            if ($format === 'epub') {
                return $this->streamEpub($novel);
            }

            $content  = $format === 'txt'
                ? $this->ebookService->buildTxtContent($novel)
                : $this->ebookService->buildFb2Content($novel);

            $filename = Str::slug($novel->title) . '.' . $format;
            $mime     = $format === 'txt' ? 'text/plain' : 'application/fb2+xml';

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => $mime]);

        } catch (\Throwable $e) {
            \Log::error('Download error: ' . $e->getMessage(), ['novel_id' => $id, 'format' => $format]);
            return back()->with('error', 'Не удалось создать файл. Попробуйте позже.');
        }
    }

    private function streamEpub(Novel $novel): \Symfony\Component\HttpFoundation\Response {
        $tmpPath = tempnam(sys_get_temp_dir(), 'epub_') . '.epub';
        $this->ebookService->buildEpubToPath($novel, $tmpPath);

        $filename = Str::slug($novel->title) . '.epub';

        return response()->download($tmpPath, $filename, ['Content-Type' => 'application/epub+zip'])
                         ->deleteFileAfterSend(true);
    }
}
