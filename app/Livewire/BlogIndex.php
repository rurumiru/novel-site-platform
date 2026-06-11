<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;

class BlogIndex extends Component {
    use WithPagination;
    public function render() {
        $recruitmentPosts = collect();
        if (Schema::hasColumn('posts', 'is_recruitment')) {
            $recruitmentPosts = Post::where('is_published', true)->where('is_recruitment', true)->latest()->take(3)->get();
        }
        return view('livewire.blog-index', [
            'posts' => Post::where('is_published', true)->latest()->paginate(10),
            'recruitmentPosts' => $recruitmentPosts,
        ])->layout('layouts.app')->title('Блог');
    }
}
