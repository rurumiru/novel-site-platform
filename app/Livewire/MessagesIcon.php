<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessagesIcon extends Component
{
    public function render()
    {
        $unreadCount = 0;
        if (Auth::check()) {
            $unreadCount = Message::where('receiver_id', Auth::id())->where('is_read', false)->count();
        }
        return view('livewire.messages-icon', ['unreadCount' => $unreadCount]);
    }
}
