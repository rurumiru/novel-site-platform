<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;

class FavoriteButton extends Component {
    public Novel $novel;
    public bool $isFavorited = false;

    public function mount(Novel $novel) {
        $this->novel = $novel;
        $this->isFavorited = Auth::check() ? $novel->favorites()->where('user_id', Auth::id())->exists() : false;
    }

    public function toggle() {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $this->novel->favorites()->toggle(Auth::id());
        $this->isFavorited = !$this->isFavorited;
    }

    public function render() {
        return view('livewire.favorite-button');
    }
}
