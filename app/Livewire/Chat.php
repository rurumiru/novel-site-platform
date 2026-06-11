<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class Chat extends Component {
    public $selectedUser = null;
    public $messageText = '';
    public $search = '';

    public function mount($user = null) {
        if ($user) {
            $this->selectedUser = User::findOrFail($user);
        } elseif (request()->has('user')) {
            $this->selectedUser = User::find(request('user'));
        }
    }

    public function selectUser($userId) {
        $this->selectedUser = User::findOrFail($userId);
        Message::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->update(['is_read' => true]);
    }

    public function sendMessage() {
        $this->validate(['messageText' => 'required|string|max:1000']);
        
        if (!$this->selectedUser) return;

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->messageText
        ]);

        $this->messageText = '';
    }

    public function reportSpam($messageId) {
        $msg = Message::findOrFail($messageId);
        if ($msg->receiver_id !== Auth::id()) abort(403);
        $msg->update([
            'is_spam' => true,
            'reported_by' => Auth::id(),
            'spam_reported_at' => now(),
        ]);
        session()->flash('success', 'Сообщение отправлено на проверку.');
    }

    public function render() {
        $myId = Auth::id();

        $users = User::where('id', '!=', $myId)
            ->where(function($q) use ($myId) {
                $q->whereHas('messagesSent', fn($m) => $m->where('receiver_id', $myId))
                  ->orWhereHas('messagesReceived', fn($m) => $m->where('sender_id', $myId));
            });
        if ($this->search) {
            $users->where('name', 'like', '%' . $this->search . '%');
        }
        $users = $users->get();

        if ($this->search && $users->isEmpty()) {
            $users = User::where('id', '!=', $myId)
                ->where('name', 'like', '%' . $this->search . '%')
                ->whereHas('novels')
                ->limit(10)
                ->get();
        }

        if ($this->selectedUser && !$users->contains('id', $this->selectedUser->id)) {
            $users->prepend($this->selectedUser);
        }

        $messages = [];
        if ($this->selectedUser) {
            $messages = Message::where(function($q) use ($myId) {
                $q->where('sender_id', $myId)->where('receiver_id', $this->selectedUser->id);
            })->orWhere(function($q) use ($myId) {
                $q->where('sender_id', $this->selectedUser->id)->where('receiver_id', $myId);
            })->orderBy('created_at', 'asc')->get();
        }

        return view('livewire.chat', compact('users', 'messages'))
            ->layout('layouts.app')
            ->title('Сообщения');
    }
}
