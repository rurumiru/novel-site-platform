<?php
namespace App\Livewire\Author;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Novel;

class MyNovels extends Component {
    public function delete($id) {
        $user = Auth::user();
        $novel = Novel::findOrFail($id);

        if ($novel->user_id !== $user->id && !$user->hasRole('super_admin')) {
            session()->flash('error', 'У вас нет прав на удаление этой новеллы.');
            return;
        }

        $novel->delete();
    }

    public function create() {
        return redirect()->route('author.novel.create');
    }

    public function render() {
        $user = Auth::user();
        
        $myNovels = $user->novels()->withCount('chapters')->latest()->get();
        $editedNovels = $user->editedNovels()->withCount('chapters')->latest()->get();
        
        $editedNovels->each(function($novel) { $novel->is_editor = true; });
        $myNovels->each(function($novel) { $novel->is_editor = false; });
        
        $allNovels = $myNovels->merge($editedNovels);

        return view('livewire.author.my-novels', [
            'novels' => $allNovels,
            'canCreate' => true
        ])
        ->layout('layouts.app')
        ->title('Мои работы');
    }
}
