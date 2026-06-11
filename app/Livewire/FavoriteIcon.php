<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;

class FavoriteIcon extends Component {
    public Novel $novel;
    public bool $isFavorited = false;

    public function mount(Novel $novel) {
        $this->novel = $novel;
        if (Auth::check()) {
            $this->isFavorited = $novel->favorites()->where('user_id', Auth::id())->exists();
        }
    }

    public function toggle() {
        if (!Auth::check()) return redirect()->route('login');
        if ($this->isFavorited) {
            $this->novel->favorites()->detach(Auth::id());
            $this->isFavorited = false;
        } else {
            $this->novel->favorites()->attach(Auth::id());
            $this->isFavorited = true;
        }
    }

    public function render() {
        return view('livewire.favorite-icon');
    }
}
