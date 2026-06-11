<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Notifications extends Component {
    use WithPagination;

    public function markAsRead($id): void {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAsReadAndRedirect($id, $url) {
        $this->markAsRead($id);
        $this->redirect($url, navigate: true);
    }

    public function markAllAsRead(): void {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render() {
        return view('livewire.notifications', [
            'notifications' => Auth::user()->notifications()->paginate(20)
        ])->layout('layouts.app')->title('Уведомления');
    }
}
