<?php

namespace App\Livewire\Author;

use Livewire\Component;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthorDashboard extends Component
{
    public function create()
    {
        return redirect()->route('author.novel.create');
    }

    public function delete($id)
    {
        $novel = Novel::findOrFail($id);
        if ($novel->user_id !== Auth::id() && !Auth::user()->hasRole(['super_admin', 'moderator'])) {
            session()->flash('error', 'Нет прав на удаление.');
            return;
        }
        $novel->delete();
        session()->flash('success', 'Новелла удалена.');
    }

    public function render()
    {
        $user = Auth::user();
        $myNovels = $user->novels()->withCount('chapters')->latest()->get();
        $editedNovels = $user->editedNovels()->withCount('chapters')->latest()->get();
        $editedNovels->each(fn($n) => $n->is_editor = true);
        $myNovels->each(fn($n) => $n->is_editor = false);
        $novels = $myNovels->merge($editedNovels);

        $stats = [
            'novels' => $user->novels()->count(),
            'chapters' => $user->novels()->withCount('chapters')->get()->sum('chapters_count'),
            'views' => $user->novels()->sum('views'),
        ];

        return view('livewire.author.author-dashboard', [
            'novels' => $novels,
            'stats' => $stats,
            'canCreate' => true,
        ])
            ->layout('layouts.app')
            ->title('Кабинет автора');
    }
}
