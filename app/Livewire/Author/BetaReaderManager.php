<?php
namespace App\Livewire\Author;

use App\Models\BetaReader;
use App\Models\BetaReaderNote;
use App\Models\Novel;
use App\Models\User;
use Livewire\Component;

class BetaReaderManager extends Component {
    public Novel $novel;
    public string $search = '';
    public array $searchResults = [];
    public string $feedback = '';
    public string $feedbackType = '';

    public function mount(Novel $novel) {
        $this->novel = $novel;
    }

    public function updatedSearch() {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }
        $existing = BetaReader::where('novel_id', $this->novel->id)->pluck('user_id');
        $this->searchResults = User::where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->whereNotIn('id', $existing)
            ->where('id', '!=', $this->novel->user_id)
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function addBetaReader(int $userId) {
        $user = User::find($userId);
        if (!$user) return;

        BetaReader::firstOrCreate(
            ['novel_id' => $this->novel->id, 'user_id' => $userId],
            ['invited_by' => auth()->id()]
        );

        $this->search = '';
        $this->searchResults = [];
        $this->feedback = "Бета-ридер {$user->name} добавлен.";
        $this->feedbackType = 'success';
    }

    public function removeBetaReader(int $userId) {
        BetaReader::where('novel_id', $this->novel->id)->where('user_id', $userId)->delete();
        $this->feedback = 'Бета-ридер удалён.';
        $this->feedbackType = 'info';
    }

    public function getBetaReadersProperty() {
        return BetaReader::where('novel_id', $this->novel->id)
            ->with('user:id,name,email')
            ->latest('id')
            ->get();
    }

    public function getNotesProperty() {
        return BetaReaderNote::where('novel_id', $this->novel->id)
            ->with(['user:id,name', 'chapter:id,title'])
            ->latest()
            ->limit(20)
            ->get();
    }

    public function render() {
        return view('livewire.author.beta-reader-manager', [
            'betaReaders' => $this->betaReaders,
            'notes'       => $this->notes,
        ]);
    }
}
