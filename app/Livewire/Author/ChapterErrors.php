<?php
namespace App\Livewire\Author;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChapterError;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;

class ChapterErrors extends Component {
    use WithPagination;

    public string $filter = 'new';

    public function markReviewed(int $id) {
        $error = $this->findError($id);
        if ($error) $error->update(['status' => 'reviewed']);
    }

    public function markFixed(int $id) {
        $error = $this->findError($id);
        if ($error) $error->update(['status' => 'fixed']);
    }

    private function findError(int $id): ?ChapterError {
        $novelIds = $this->getNovelIds();
        return ChapterError::where('id', $id)
            ->whereHas('chapter', fn($q) => $q->whereIn('novel_id', $novelIds))
            ->first();
    }

    private function getNovelIds(): array {
        $user = Auth::user();
        $owned = $user->novels()->pluck('id');
        $edited = $user->editedNovels()->pluck('novels.id');
        return $owned->merge($edited)->unique()->toArray();
    }

    public function updatedFilter() {
        $this->resetPage();
    }

    public function render() {
        $novelIds = $this->getNovelIds();

        $errors = ChapterError::whereHas('chapter', fn($q) => $q->whereIn('novel_id', $novelIds))
            ->with(['chapter:id,title,novel_id', 'chapter.novel:id,title', 'user:id,name'])
            ->when($this->filter !== 'all', fn($q) => $q->where('status', $this->filter))
            ->latest()
            ->paginate(20);

        $counts = [
            'new' => ChapterError::whereHas('chapter', fn($q) => $q->whereIn('novel_id', $novelIds))->where('status', 'new')->count(),
            'reviewed' => ChapterError::whereHas('chapter', fn($q) => $q->whereIn('novel_id', $novelIds))->where('status', 'reviewed')->count(),
            'fixed' => ChapterError::whereHas('chapter', fn($q) => $q->whereIn('novel_id', $novelIds))->where('status', 'fixed')->count(),
        ];

        return view('livewire.author.chapter-errors', [
            'errors' => $errors,
            'counts' => $counts,
        ])->layout('layouts.app')->title('Ошибки в тексте');
    }
}
