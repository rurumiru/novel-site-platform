<?php
namespace App\Livewire\Author;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Novel;
use App\Models\Chapter;
use App\Services\ChapterImporter;

class ChapterManager extends Component {
    use WithFileUploads;

    public Novel $novel;
    public $chapters;
    public $volumes;
    public bool $isEditor = false;
    public bool $editorCanReadPaid = false;

    public $importFile;
    public $importIsLocked = false;
    public $importPrice = 0;
    public $showImport = false;

    public function mount(Novel $novel, bool $isEditor = false, bool $editorCanReadPaid = false) {
        $this->novel = $novel;
        $this->isEditor = $isEditor;
        $this->editorCanReadPaid = $editorCanReadPaid;
        $this->initializeSortOrders();
        $this->loadChapters();
    }

    private function initializeSortOrders(): void {
        $hasZeroVolumes  = $this->novel->volumes()->where('sort_order', 0)->exists();
        $hasZeroChapters = $this->novel->chapters()->where('sort_order', 0)->exists();

        if ($hasZeroVolumes) {
            $ids = $this->novel->volumes()->orderBy('id')->pluck('id')->toArray();
            foreach ($ids as $pos => $id) {
                \App\Models\Volume::where('id', $id)->update(['sort_order' => $pos + 1]);
            }
        }

        if ($hasZeroVolumes || $hasZeroChapters) {
            $this->novel->recalculateChapterSortOrders();
        }
    }

    public function loadChapters() {
        $chapterQuery = $this->novel->chapters()->orderBy('sort_order');
        if ($this->isEditor && !$this->editorCanReadPaid) {
            $chapterQuery->where('is_locked', false);
        }
        $this->chapters = $chapterQuery->get();

        $this->volumes = $this->novel->volumes()
            ->orderBy('sort_order')
            ->with(['chapters' => function ($q) {
                $q->orderBy('sort_order');
                if ($this->isEditor && !$this->editorCanReadPaid) {
                    $q->where('is_locked', false);
                }
            }])
            ->get();
    }

    public function addVolume() {
        $maxOrder = $this->novel->volumes()->max('sort_order') ?? 0;
        $this->novel->volumes()->create([
            'title' => 'Том ' . ($maxOrder + 1),
            'sort_order' => $maxOrder + 1,
        ]);
        $this->loadChapters();
        session()->flash('success', 'Том добавлен');
    }

    public function renameVolume(int $volumeId, string $title) {
        $title = trim($title);
        if (empty($title)) return;
        $this->novel->volumes()->findOrFail($volumeId)->update(['title' => $title]);
        $this->loadChapters();
    }

    public function deleteVolume(int $volumeId) {
        $volume = $this->novel->volumes()->findOrFail($volumeId);
        Chapter::where('novel_id', $this->novel->id)
            ->where('volume_id', $volumeId)
            ->update(['volume_id' => null]);
        $volume->delete();
        $this->novel->recalculateChapterSortOrders();
        $this->loadChapters();
        session()->flash('success', 'Том удалён, главы перемещены в «Без тома»');
    }

    private function getVolumeIds(): array {
        return $this->novel->volumes()
            ->orderBy('sort_order')->orderBy('id')
            ->pluck('id')->toArray();
    }

    private function applyVolumeOrder(array $orderedIds): void {
        foreach ($orderedIds as $pos => $id) {
            \App\Models\Volume::where('id', $id)->update(['sort_order' => $pos + 1]);
        }
        $this->novel->recalculateChapterSortOrders();
    }

    public function moveVolumeUp($volumeId) {
        $ids   = $this->getVolumeIds();
        $index = array_search((int) $volumeId, $ids, true);
        if ($index === false || $index === 0) { $this->loadChapters(); return; }
        [$ids[$index], $ids[$index - 1]] = [$ids[$index - 1], $ids[$index]];
        $this->applyVolumeOrder($ids);
        $this->loadChapters();
    }

    public function moveVolumeDown($volumeId) {
        $ids   = $this->getVolumeIds();
        $index = array_search((int) $volumeId, $ids, true);
        if ($index === false || $index >= count($ids) - 1) { $this->loadChapters(); return; }
        [$ids[$index], $ids[$index + 1]] = [$ids[$index + 1], $ids[$index]];
        $this->applyVolumeOrder($ids);
        $this->loadChapters();
    }

    private function getSiblingIds(?int $volumeId): array {
        return Chapter::where('novel_id', $this->novel->id)
            ->where('volume_id', $volumeId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
    }

    private function applySiblingOrder(array $orderedIds): void {
        foreach ($orderedIds as $pos => $id) {
            Chapter::where('id', $id)->update(['sort_order' => $pos + 1]);
        }
        $this->novel->recalculateChapterSortOrders();
    }

    public function moveUp(int $chapterId): void {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $ids     = $this->getSiblingIds($chapter->volume_id);
        $index   = array_search($chapterId, $ids, true);

        if ($index === false || $index === 0) { $this->loadChapters(); return; }

        [$ids[$index], $ids[$index - 1]] = [$ids[$index - 1], $ids[$index]];
        $this->applySiblingOrder($ids);
        $this->loadChapters();
    }

    public function moveDown(int $chapterId): void {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $ids     = $this->getSiblingIds($chapter->volume_id);
        $index   = array_search($chapterId, $ids, true);

        if ($index === false || $index >= count($ids) - 1) { $this->loadChapters(); return; }

        [$ids[$index], $ids[$index + 1]] = [$ids[$index + 1], $ids[$index]];
        $this->applySiblingOrder($ids);
        $this->loadChapters();
    }

    public function reorderChapter(int $chapterId, int $targetId): void {
        if ($chapterId === $targetId) return;

        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $target  = Chapter::where('novel_id', $this->novel->id)->findOrFail($targetId);

        if ($chapter->volume_id == $target->volume_id) {
            $ids     = $this->getSiblingIds($chapter->volume_id);
            $fromIdx = array_search($chapterId, $ids, true);
            $toIdx   = array_search($targetId,  $ids, true);
            if ($fromIdx === false || $toIdx === false) { $this->loadChapters(); return; }

            array_splice($ids, $fromIdx, 1);
            $newTo = array_search($targetId, $ids, true);
            $insertAt = $newTo === false ? count($ids) : $newTo;
            array_splice($ids, $insertAt, 0, [$chapterId]);
        } else {
            $chapter->update(['volume_id' => $target->volume_id]);
            $ids   = $this->getSiblingIds($target->volume_id);
            $ids   = array_values(array_filter($ids, fn($id) => $id !== $chapterId));
            $toIdx = array_search($targetId, $ids, true);
            $insertAt = $toIdx === false ? count($ids) : $toIdx;
            array_splice($ids, $insertAt, 0, [$chapterId]);
        }

        $this->applySiblingOrder($ids);
        $this->loadChapters();
    }

    public function moveToVolume(int $chapterId, $volumeId = null): void {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $vid = $volumeId && $volumeId !== '' ? (int) $volumeId : null;
        $this->novel->moveChapterToVolume($chapter, $vid);
        $this->novel->recalculateChapterSortOrders();
        $this->loadChapters();
        session()->flash('success', 'Глава перенесена');
    }

    public function reorderGroup(array $orderedIds, $volumeId = null): void {
        if (empty($orderedIds)) return;

        $vid = ($volumeId === null || $volumeId === '' || $volumeId === 0 || $volumeId === '0') ? null : (int) $volumeId;
        $orderedIds = array_map('intval', $orderedIds);

        $valid = Chapter::where('novel_id', $this->novel->id)
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->toArray();
        $orderedIds = array_values(array_intersect($orderedIds, $valid));

        Chapter::where('novel_id', $this->novel->id)
            ->whereIn('id', $orderedIds)
            ->update(['volume_id' => $vid]);

        foreach ($orderedIds as $pos => $id) {
            Chapter::where('id', $id)->update(['sort_order' => $pos + 1]);
        }
        $this->novel->recalculateChapterSortOrders();
        $this->loadChapters();
    }

    public function moveToTop(int $chapterId): void {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $ids     = $this->getSiblingIds($chapter->volume_id);
        $ids     = array_values(array_filter($ids, fn($id) => $id !== $chapterId));
        array_unshift($ids, $chapterId);
        $this->applySiblingOrder($ids);
        $this->loadChapters();
        session()->flash('success', 'Глава перемещена в начало');
    }

    public function moveToBottom(int $chapterId): void {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $ids     = $this->getSiblingIds($chapter->volume_id);
        $ids     = array_values(array_filter($ids, fn($id) => $id !== $chapterId));
        $ids[]   = $chapterId;
        $this->applySiblingOrder($ids);
        $this->loadChapters();
        session()->flash('success', 'Глава перемещена в конец');
    }

    public function moveToPosition(int $chapterId, int $position): void {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $ids     = $this->getSiblingIds($chapter->volume_id);
        $ids     = array_values(array_filter($ids, fn($id) => $id !== $chapterId));

        $position = max(1, min($position, count($ids) + 1));
        array_splice($ids, $position - 1, 0, [$chapterId]);

        $this->applySiblingOrder($ids);
        $this->loadChapters();
        session()->flash('success', "Глава перемещена на позицию {$position}");
    }

    public function delete($chapterId) {
        Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId)->delete();
        $this->loadChapters();
        session()->flash('success', 'Глава удалена');
    }

    public function bulkMakeFree(array $ids): void {
        if (empty($ids)) return;
        $ids = array_map('intval', $ids);
        Chapter::where('novel_id', $this->novel->id)
            ->whereIn('id', $ids)
            ->update(['is_locked' => false, 'price' => 0]);
        $this->loadChapters();
        session()->flash('success', 'Выбранные главы сделаны бесплатными');
    }

    public function bulkMakePaid(array $ids, int $price): void {
        if (empty($ids) || $price <= 0) return;
        $ids = array_map('intval', $ids);
        Chapter::where('novel_id', $this->novel->id)
            ->whereIn('id', $ids)
            ->update(['is_locked' => true, 'price' => $price]);
        $this->loadChapters();
        session()->flash('success', "Выбранные главы сделаны платными по {$price} ₽");
    }

    public function bulkDelete(array $ids): void {
        if (empty($ids)) return;
        $ids = array_map('intval', $ids);
        Chapter::where('novel_id', $this->novel->id)
            ->whereIn('id', $ids)
            ->delete();
        $this->novel->recalculateChapterSortOrders();
        $this->loadChapters();
        $count = count($ids);
        session()->flash('success', "Удалено глав: {$count}");
    }

    public $splitChapterId = null;
    public $splitParagraphs = [];
    public $splitPoints = [];
    public $showSplit = false;

    public function openSplit(int $chapterId) {
        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($chapterId);
        $this->splitChapterId = $chapter->id;

        $html = $chapter->content ?? '';
        $text = \App\Services\ContentRenderer::toHtml($html);

        $paragraphs = preg_split('/\n{2,}|<\/p>\s*<p|<br\s*\/?>\s*<br\s*\/?>/', strip_tags($text, '<b><i><em><strong>'));
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), fn($p) => mb_strlen($p) > 0));

        $this->splitParagraphs = [];
        foreach ($paragraphs as $i => $p) {
            $words = str_word_count(strip_tags($p), 0, 'АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдежзийклмнопрстуфхцчшщъыьэюя');
            $this->splitParagraphs[] = [
                'index' => $i,
                'text' => mb_substr(strip_tags($p), 0, 300),
                'words' => $words,
            ];
        }

        $this->splitPoints = [];
        $this->showSplit = true;
    }

    public function toggleSplitPoint(int $index) {
        if (in_array($index, $this->splitPoints)) {
            $this->splitPoints = array_values(array_diff($this->splitPoints, [$index]));
        } else {
            $this->splitPoints[] = $index;
            sort($this->splitPoints);
        }
    }

    public function executeSplit() {
        if (empty($this->splitPoints) || !$this->splitChapterId) return;

        $chapter = Chapter::where('novel_id', $this->novel->id)->findOrFail($this->splitChapterId);
        $html = $chapter->content ?? '';
        $text = \App\Services\ContentRenderer::toHtml($html);

        $rawParagraphs = preg_split('/\n{2,}|<\/p>\s*<p|<br\s*\/?>\s*<br\s*\/?>/', strip_tags($text, '<b><i><em><strong>'));
        $rawParagraphs = array_values(array_filter(array_map('trim', $rawParagraphs), fn($p) => mb_strlen($p) > 0));

        $groups = [];
        $prev = 0;
        $points = array_unique($this->splitPoints);
        sort($points);

        foreach ($points as $point) {
            $groups[] = array_slice($rawParagraphs, $prev, $point - $prev);
            $prev = $point;
        }
        $groups[] = array_slice($rawParagraphs, $prev);

        $groups = array_values(array_filter($groups, fn($g) => count($g) > 0));

        if (count($groups) < 2) {
            session()->flash('error', 'Нечего разделять.');
            return;
        }

        $chapter->update(['content' => \App\Services\ContentRenderer::plainToHtml(implode("\n\n", $groups[0]))]);

        $baseSortOrder = $chapter->sort_order;
        $baseTitle = preg_replace('/\s*\(часть\s*\d+\)$/iu', '', $chapter->title);

        $chapter->update(['title' => $baseTitle . ' (часть 1)']);

        Chapter::where('novel_id', $this->novel->id)
            ->where('sort_order', '>', $baseSortOrder)
            ->increment('sort_order', count($groups) - 1);

        for ($i = 1; $i < count($groups); $i++) {
            Chapter::create([
                'novel_id'     => $this->novel->id,
                'title'        => $baseTitle . ' (часть ' . ($i + 1) . ')',
                'content'      => \App\Services\ContentRenderer::plainToHtml(implode("\n\n", $groups[$i])),
                'sort_order'   => $baseSortOrder + $i,
                'is_published' => $chapter->is_published,
                'is_locked'    => $chapter->is_locked,
                'price'        => $chapter->price,
                'volume_id'    => $chapter->volume_id,
            ]);
        }

        $this->showSplit = false;
        $this->splitParagraphs = [];
        $this->splitPoints = [];
        $this->splitChapterId = null;
        $this->novel->recalculateChapterSortOrders();
        $this->loadChapters();
        session()->flash('success', 'Глава разделена на ' . count($groups) . ' частей.');
    }

    public function cancelSplit() {
        $this->showSplit = false;
        $this->splitParagraphs = [];
        $this->splitPoints = [];
        $this->splitChapterId = null;
    }

    public $importPreview = [];
    public $importVolumeId = null;

    public function previewImport() {
        $this->validate([
            'importFile' => 'required|file|mimes:txt,fb2,epub,doc,docx|max:20480',
        ]);

        $path = $this->importFile->getRealPath();
        $ext  = strtolower($this->importFile->getClientOriginalExtension());
        $text = ChapterImporter::extractTextFromFile($path, $ext);
        $chapters = ChapterImporter::parseChaptersFromText($text);

        $this->importPreview = collect($chapters)->map(fn($ch, $i) => [
            'title' => mb_substr($ch['title'], 0, 100),
            'length' => mb_strlen($ch['content']),
            'preview' => mb_substr(strip_tags($ch['content']), 0, 150) . '...',
        ])->toArray();
    }

    public function processImport() {
        $this->validate([
            'importFile'  => 'required|file|mimes:txt,fb2,epub,doc,docx|max:20480',
            'importPrice' => $this->importIsLocked ? 'required|integer|min:1' : 'nullable|integer|min:0',
        ], [
            'importPrice.min' => 'Цена платной главы должна быть не менее 1 ₽.',
        ]);

        $path = $this->importFile->getRealPath();
        $ext  = strtolower($this->importFile->getClientOriginalExtension());
        $text = ChapterImporter::extractTextFromFile($path, $ext);

        $count = ChapterImporter::importFromText($this->novel, $text, [
            'is_locked' => $this->importIsLocked,
            'price'     => (int) $this->importPrice,
            'volume_id' => $this->importVolumeId ?: null,
        ]);

        $this->showImport = false;
        $this->importFile = null;
        $this->importPreview = [];
        $this->importVolumeId = null;
        $this->loadChapters();
        $this->novel->recalculateChapterSortOrders();
        session()->flash('success', "Импортировано глав: $count");
    }

    public function render() {
        return view('livewire.author.chapter-manager');
    }
}
