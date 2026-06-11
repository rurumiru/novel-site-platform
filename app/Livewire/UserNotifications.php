<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UserNotifications extends Component {
    use WithPagination;

    public string $tab = 'all';

    private array $tabTypes = [
        'all'      => [],
        'comments' => ['new_comment'],
        'errors'   => ['chapter_error', 'error_resolved'],
        'updates'  => ['new_chapter', 'beta_chapter'],
        'messages' => ['new_message'],
    ];

    public function setTab(string $tab) {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function markAsRead($id) {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) $notification->markAsRead();
    }

    public function markAsReadAndRedirect($id, $url) {
        $this->markAsRead($id);
        $this->redirect($url, navigate: true);
    }

    public function markAllAsRead() {
        $query = Auth::user()->unreadNotifications();
        $types = $this->tabTypes[$this->tab] ?? [];
        if (!empty($types)) {
            $query->where(function ($q) use ($types) {
                foreach ($types as $t) {
                    $q->orWhereJsonContains('data->type', $t);
                }
            });
        }
        $query->get()->markAsRead();
    }

    public function render() {
        $query = Auth::user()->notifications();
        $types = $this->tabTypes[$this->tab] ?? [];
        if (!empty($types)) {
            $query->where(function ($q) use ($types) {
                foreach ($types as $t) {
                    $q->orWhereJsonContains('data->type', $t);
                }
            });
        }

        $counts = [];
        foreach ($this->tabTypes as $key => $tTypes) {
            $q = Auth::user()->unreadNotifications();
            if (!empty($tTypes)) {
                $q->where(function ($sq) use ($tTypes) {
                    foreach ($tTypes as $t) $sq->orWhereJsonContains('data->type', $t);
                });
            }
            $counts[$key] = $q->count();
        }

        return view('livewire.user-notifications', [
            'notifications' => $query->paginate(20),
            'counts' => $counts,
        ])->layout('layouts.app')->title('Уведомления');
    }
}
