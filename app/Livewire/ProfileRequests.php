<?php
namespace App\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;

class ProfileRequests extends Component {
    public function approve($id) {
        $sub = Subscription::findOrFail($id);
        if ($sub->author_id !== Auth::id()) abort(403);
        $sub->update(['status' => 'active']);
    }

    public function reject($id) {
        $sub = Subscription::findOrFail($id);
        if ($sub->author_id !== Auth::id()) abort(403);
        $sub->update(['status' => 'rejected']);
    }

    public function render() {
        $requests = Subscription::where('author_id', Auth::id())
            ->where('status', 'pending')
            ->with(['user', 'novel'])
            ->latest()
            ->get();

        return view('livewire.profile-requests', compact('requests'));
    }
}
