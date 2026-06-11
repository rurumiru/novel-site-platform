<?php
namespace App\Livewire\Author;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Novel;
use App\Models\Chapter;
use App\Services\ChapterImporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChapterImport extends Component {
    use WithFileUploads;

    public Novel $novel;
    public $file;
    public string $pastedText = '';

    public string $step = 'upload';

    public array $paragraphs = [];
    public int $totalParagraphs = 0;
    public int $totalWords = 0;
    public array $splitPoints = [];
    public string $sourceInfo = '';
    public string $cacheKey = '';

    public ?int $volumeId = null;
    public bool $isLocked = false;
    public int $price = 0;

    public function mount($id) {
        $novel = Novel::findOrFail($id);
        $isOwner = $novel->user_id === Auth::id();
        $isAdmin = Auth::user()->hasRole(['super_admin', 'moderator']);
        $isEditor = $novel->editors()->where('users.id', Auth::id())->exists();
        if (!$isOwner && !$isAdmin && !$isEditor) abort(403);
        $this->novel = $novel;
        $this->cacheKey = 'import_' . Auth::id() . '_' . $novel->id . '_' . time();
    }

    public function parseFile() {
        $this->validate(['file' => 'required|file|mimes:txt,fb2,epub,doc,docx|max:20480']);
        $path = $this->file->getRealPath();
        $ext = strtolower($this->file->getClientOriginalExtension());
        $text = ChapterImporter::extractTextFromFile($path, $ext);
        $this->sourceInfo = $this->file->getClientOriginalName();
        $this->processText($text);
    }

    public function parseText() {
        $this->validate(['pastedText' => 'required|string|min:50']);
        $this->sourceInfo = 'Вставленный текст';
        $this->processText($this->pastedText);
    }

    private function processText(string $text) {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        $blocks = preg_split('/\n{2,}/', $text);
        $fullParagraphs = [];
        $this->paragraphs = [];
        $this->totalWords = 0;

        $headerPatterns = [
            '/^Глава\s*№?\s*\d+/iu', '/^Chapter\s*\d+/iu', '/^Part\s+\d+/iu',
            '/^Часть\s+\d+/iu', '/^Том\s+\d+/iu', '/^Пролог/iu', '/^Эпилог/iu', '/^\d+\.\s+\S/u',
        ];

        foreach ($blocks as $block) {
            $block = trim(strip_tags($block));
            if ($block === '') continue;
            $words = preg_match_all('/[\wА-Яа-яЁё]+/u', $block);
            $this->totalWords += $words;

            $isHeader = false;
            foreach ($headerPatterns as $pat) {
                if (preg_match($pat, $block)) { $isHeader = true; break; }
            }

            $fullParagraphs[] = $block;
            $this->paragraphs[] = [
                'preview' => mb_substr($block, 0, 250),
                'words' => $words,
                'charCount' => mb_strlen($block),
                'isHeader' => $isHeader,
                'trimmed' => mb_strlen($block) > 250,
            ];
        }

        $this->totalParagraphs = count($fullParagraphs);

        Cache::put($this->cacheKey, $fullParagraphs, now()->addMinutes(30));

        $this->splitPoints = [];
        foreach ($this->paragraphs as $i => $p) {
            if ($i === 0) continue;
            if ($p['isHeader']) $this->splitPoints[] = $i;
        }

        $this->pastedText = '';
        $this->step = 'split';
    }

    public function toggleSplit(int $index) {
        if ($index <= 0 || $index >= count($this->paragraphs)) return;
        if (in_array($index, $this->splitPoints)) {
            $this->splitPoints = array_values(array_diff($this->splitPoints, [$index]));
        } else {
            $this->splitPoints[] = $index;
            sort($this->splitPoints);
        }
    }

    public function clearAllSplits() { $this->splitPoints = []; }

    public function autoDetectSplits() {
        $this->splitPoints = [];
        foreach ($this->paragraphs as $i => $p) {
            if ($i === 0) continue;
            if ($p['isHeader']) $this->splitPoints[] = $i;
        }
    }

    public function getChaptersProperty(): array {
        if (empty($this->paragraphs)) return [];

        $points = $this->splitPoints;
        sort($points);
        $chapters = [];
        $prev = 0;

        foreach ($points as $point) {
            $slice = array_slice($this->paragraphs, $prev, $point - $prev);
            if (!empty($slice)) $chapters[] = $this->chapterMeta($slice, count($chapters) + 1);
            $prev = $point;
        }
        $slice = array_slice($this->paragraphs, $prev);
        if (!empty($slice)) $chapters[] = $this->chapterMeta($slice, count($chapters) + 1);

        return $chapters;
    }

    private function chapterMeta(array $paras, int $num): array {
        $first = $paras[0]['preview'] ?? '';
        $isH = $paras[0]['isHeader'] ?? false;
        return [
            'title' => $isH ? mb_substr($first, 0, 120) : 'Глава ' . $num,
            'words' => array_sum(array_column($paras, 'words')),
            'chars' => array_sum(array_column($paras, 'charCount')),
            'paras' => count($paras),
        ];
    }

    public function executeImport() {
        $full = Cache::get($this->cacheKey, []);
        if (empty($full)) {
            session()->flash('error', 'Сессия истекла. Загрузите файл заново.');
            $this->step = 'upload';
            return;
        }

        $points = $this->splitPoints;
        sort($points);
        $groups = [];
        $prev = 0;
        foreach ($points as $p) { $groups[] = array_slice($full, $prev, $p - $prev); $prev = $p; }
        $groups[] = array_slice($full, $prev);
        $groups = array_values(array_filter($groups, fn($g) => !empty($g)));

        $maxOrder = $this->novel->chapters()->max('sort_order') ?? 0;
        $count = 0;
        $headerPats = ['/^(Глава|Chapter|Part|Часть|Том|Пролог|Эпилог|\d+\.)\s*/iu'];

        foreach ($groups as $n => $paras) {
            if (empty($paras)) continue;
            $first = $paras[0];
            $isH = false;
            foreach ($headerPats as $pat) { if (preg_match($pat, $first)) { $isH = true; break; } }
            $title = $isH ? mb_substr($first, 0, 240) : 'Глава ' . ($n + 1);
            $title = trim(preg_replace('/\s+/u', ' ', $title));
            $body = implode("\n\n", $isH ? array_slice($paras, 1) : $paras);
            if (mb_strlen(trim($body)) < 5) continue;

            $bodyHtml = \App\Services\ContentRenderer::plainToHtml($body);
            if ($bodyHtml === '') continue;

            $maxOrder++;
            Chapter::create([
                'novel_id' => $this->novel->id,
                'title' => $title,
                'content' => $bodyHtml,
                'sort_order' => $maxOrder,
                'is_published' => true,
                'is_locked' => $this->isLocked,
                'price' => $this->isLocked ? max(1, $this->price) : 0,
                'volume_id' => $this->volumeId ?: null,
            ]);
            $count++;
        }

        $this->novel->recalculateChapterSortOrders();
        Cache::forget($this->cacheKey);
        session()->flash('success', "Импортировано глав: $count");
        return $this->redirect(route('author.novel.edit', $this->novel->id), navigate: true);
    }

    public function backToUpload() {
        Cache::forget($this->cacheKey);
        $this->step = 'upload';
        $this->paragraphs = [];
        $this->splitPoints = [];
        $this->file = null;
        $this->pastedText = '';
    }

    public function render() {
        return view('livewire.author.chapter-import', [
            'chapters' => $this->chapters,
            'volumes' => $this->novel->volumes()->orderBy('sort_order')->get(),
        ])->layout('layouts.app')->title('Импорт глав — ' . $this->novel->title);
    }
}
