<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;

class RecruitmentIndex extends Component
{
    public function render()
    {
        $posts = collect();
        if (Schema::hasColumn('posts', 'is_recruitment')) {
            $posts = Post::where('is_published', true)
                ->where('is_recruitment', true)
                ->latest()
                ->paginate(12);
        }

        return view('livewire.recruitment-index', [
            'posts' => $posts,
        ])->layout('layouts.app')->title('Набор модераторов и работников');
    }
}
