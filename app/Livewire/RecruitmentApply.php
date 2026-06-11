<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\RecruitmentApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RecruitmentApply extends Component
{
    public Post $post;
    public string $name = '';
    public string $email = '';
    public string $contact = '';
    public string $experience = '';
    public string $motivation = '';
    public string $skills = '';
    public bool $success = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contact' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:2000',
            'motivation' => 'nullable|string|max:2000',
            'skills' => 'nullable|string|max:2000',
        ];
    }

    public function mount($slug)
    {
        $this->post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->when(Schema::hasColumn('posts', 'is_recruitment'), fn($q) => $q->where('is_recruitment', true))
            ->firstOrFail();

        if (Auth::check()) {
            $this->name = Auth::user()->name;
            $this->email = Auth::user()->email;
        }
    }

    public function submit()
    {
        $this->validate();

        if (!Schema::hasTable('recruitment_applications')) {
            session()->flash('error', 'Таблица откликов ещё не создана. Выполните: php artisan migrate --force');
            return;
        }

        $user = Auth::user();
        RecruitmentApplication::create([
            'post_id' => $this->post->id,
            'user_id' => $user?->id,
            'name' => $this->name,
            'email' => $this->email,
            'contact' => $this->contact ?: null,
            'experience' => $this->experience ?: null,
            'motivation' => $this->motivation ?: null,
            'skills' => $this->skills ?: null,
        ]);

        $this->success = true;
    }

    public function render()
    {
        return view('livewire.recruitment-apply')
            ->layout('layouts.app')
            ->title('Отклик: ' . $this->post->title);
    }
}
