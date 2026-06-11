<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Chapter;
use Illuminate\Support\Facades\Auth;

class ChapterLike extends Component {
    public Chapter $chapter;
    public bool $isLiked = false;
    public int $count = 0;

    public function mount(Chapter $chapter) {
        $this->chapter = $chapter;
        $this->count = $chapter->likes()->count();
        if (Auth::check()) {
            $this->isLiked = $chapter->likes()->where('user_id', Auth::id())->exists();
        }
    }

    public function toggle() {
        if (!Auth::check()) return redirect()->route('login');

        if ($this->isLiked) {
            $this->chapter->likes()->detach(Auth::id());
            $this->isLiked = false;
            $this->count--;
        } else {
            $this->chapter->likes()->attach(Auth::id());
            $this->isLiked = true;
            $this->count++;
        }
    }

    public function render() {
        return view('livewire.chapter-like');
    }
}
