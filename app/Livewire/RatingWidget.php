<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;

class RatingWidget extends Component {
    public Novel $novel;
    public int $myRating = 0;

    public function mount(Novel $novel) {
        $this->novel = $novel;
        if (Auth::check()) {
            $rating = $novel->ratings()->where('user_id', Auth::id())->first();
            $this->myRating = $rating ? (int) $rating->score : 0;
        }
    }

    public function rate($score) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $this->novel->ratings()->updateOrCreate(
            ['user_id' => Auth::id()],
            ['score' => $score]
        );
        
        $this->myRating = $score;
    }

    public function render() {
        return view('livewire.rating-widget');
    }
}
