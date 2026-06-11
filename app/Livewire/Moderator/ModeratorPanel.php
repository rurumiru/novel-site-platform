<?php

namespace App\Livewire\Moderator;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Novel;
use App\Models\Comment;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ModeratorPanel extends Component
{
    use WithPagination;

    public $tab = 'novels';
    public $novelFilter = 'all';
    public $search = '';

    public function approveNovel($id)
    {
        $novel = Novel::findOrFail($id);
        if (!Auth::user()->hasRole(['super_admin', 'moderator'])) abort(403);
        $novel->update([
            'moderation_status' => 'approved',
            'is_published' => true,
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);
        session()->flash('success', 'Новелла одобрена и опубликована.');
    }

    public function rejectNovel($id)
    {
        $novel = Novel::findOrFail($id);
        if (!Auth::user()->hasRole(['super_admin', 'moderator'])) abort(403);
        $novel->update([
            'moderation_status' => 'rejected',
            'is_published' => false,
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);
        session()->flash('success', 'Новелла отклонена.');
    }

    public function unpublishNovel($id)
    {
        $novel = Novel::findOrFail($id);
        if (!Auth::user()->hasRole(['super_admin', 'moderator'])) abort(403);
        $novel->update(['is_published' => false]);
        session()->flash('success', 'Новелла снята с публикации.');
    }

    public function publishNovel($id)
    {
        $novel = Novel::findOrFail($id);
        if (!Auth::user()->hasRole(['super_admin', 'moderator'])) abort(403);
        $novel->update([
            'is_published' => true,
            'moderation_status' => 'approved',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);
        session()->flash('success', 'Новелла опубликована.');
    }

    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);
        if (!Auth::user()->hasRole(['super_admin', 'moderator'])) abort(403);
        $comment->delete();
        session()->flash('success', 'Комментарий удалён.');
    }

    public function approveCover($id)
    {
        Novel::findOrFail($id)->update(['cover_moderation_status' => 'approved']);
        session()->flash('success', 'Обложка одобрена.');
    }

    public function rejectCover($id)
    {
        Novel::findOrFail($id)->update(['cover_moderation_status' => 'rejected']);
        session()->flash('success', 'Обложка отклонена.');
    }

    public function approveDescription($id)
    {
        Novel::findOrFail($id)->update(['description_moderation_status' => 'approved']);
        session()->flash('success', 'Описание одобрено.');
    }

    public function rejectDescription($id)
    {
        Novel::findOrFail($id)->update(['description_moderation_status' => 'rejected']);
        session()->flash('success', 'Описание отклонено.');
    }

    public function dismissSpamMessage($id)
    {
        Message::findOrFail($id)->update(['is_spam' => false, 'reported_by' => null, 'spam_reported_at' => null]);
        session()->flash('success', 'Жалоба снята.');
    }

    public function deleteSpamMessage($id)
    {
        Message::findOrFail($id)->delete();
        session()->flash('success', 'Сообщение удалено.');
    }

    public function render()
    {
        if (!Auth::user()->hasRole(['super_admin', 'moderator'])) {
            return redirect()->route('home');
        }

        $novelsQuery = Novel::with(['publisher:id,name', 'tags'])
            ->withCount('chapters');

        if ($this->novelFilter === 'pending') {
            $novelsQuery->where('moderation_status', 'pending');
        } elseif ($this->novelFilter === 'rejected') {
            $novelsQuery->where('moderation_status', 'rejected');
        } elseif ($this->novelFilter === 'approved') {
            $novelsQuery->where('moderation_status', 'approved');
        } elseif ($this->novelFilter === 'content') {
            $novelsQuery->where(function ($q) {
                $q->where('cover_moderation_status', 'pending')->orWhere('description_moderation_status', 'pending');
            });
        }

        if ($this->search) {
            $novelsQuery->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('author_name', 'like', '%' . $this->search . '%');
            });
        }

        $novels = $novelsQuery->latest()->paginate(15);
        $comments = Comment::with(['user', 'commentable'])->latest()->paginate(20, ['*'], 'comments_page');
        $spamMessages = Message::with(['sender', 'receiver', 'reporter'])->where('is_spam', true)->latest('spam_reported_at')->paginate(20, ['*'], 'spam_page');

        return view('livewire.moderator.moderator-panel', [
            'novels' => $novels,
            'comments' => $comments,
            'spamMessages' => $spamMessages,
        ])
            ->layout('layouts.app')
            ->title('Панель модератора');
    }
}
