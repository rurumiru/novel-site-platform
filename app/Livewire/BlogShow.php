<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Post;
use Illuminate\Support\Facades\Session;

class BlogShow extends Component {
    public Post $post;

    public function mount($slug) {
        $this->post = Post::where('slug', $slug)->where('is_published', true)->firstOrFail();
        
        $key = 'viewed_post_' . $this->post->id;
        if (!Session::has($key)) {
            $this->post->increment('views');
            Session::put($key, true);
        }
    }

    public function render() {
        return view('livewire.blog-show')->layout('layouts.app')->title($this->post->title);
    }
}
