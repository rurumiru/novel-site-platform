<?php
namespace App\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsIcon extends Component {
    public function render() {
        $unreadCount = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
        return view('livewire.notifications-icon', compact('unreadCount'));
    }
}
