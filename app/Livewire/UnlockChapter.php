<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Chapter;
use App\Models\PromoCodeUsage;
use Illuminate\Support\Facades\Auth;

class UnlockChapter extends Component {
    public Chapter $chapter;

    public function mount(Chapter $chapter) {
        $this->chapter = $chapter;
    }

    public function unlock() {
        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();
        if (!$user->hasVerifiedEmail()) {
            session()->flash('error', 'Подтвердите почту, чтобы покупать платные главы.');
            return;
        }

        $price    = $this->chapter->price;
        $discount = $this->getActiveDiscount($user);

        if ($discount) {
            $percent = $discount->promoCode->value;
            $price   = max(0, (int) round($price * (1 - $percent / 100)));
        }

        if ($price > 0 && $user->balance < $price) {
            session()->flash('error', 'Недостаточно монет! Пополните баланс.');
            return;
        }

        if ($price > 0) {
            $user->decrement('balance', $price);
        }
        $user->unlockedChapters()->attach($this->chapter->id, ['price_paid' => $price]);

        if ($discount) {
            $discount->update(['applied_at' => now()]);
        }

        return redirect()->route('novel.read', [$this->chapter->novel_id, $this->chapter->id]);
    }

    private function getActiveDiscount($user): ?PromoCodeUsage
    {
        return PromoCodeUsage::where('user_id', $user->id)
            ->whereNull('applied_at')
            ->whereHas('promoCode', function ($q) {
                $q->where('type', 'discount')
                  ->where('is_active', true)
                  ->where(function ($q2) {
                      $q2->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                  })
                  ->where(function ($q2) {
                      $q2->whereNull('novel_id')
                         ->orWhere('novel_id', $this->chapter->novel_id);
                  });
            })
            ->with('promoCode')
            ->first();
    }

    public function render() {
        $discount      = null;
        $discountPrice = null;

        if (Auth::check()) {
            $discount = $this->getActiveDiscount(Auth::user());
            if ($discount) {
                $percent       = $discount->promoCode->value;
                $discountPrice = max(0, (int) round($this->chapter->price * (1 - $percent / 100)));
            }
        }

        return view('livewire.unlock-chapter', compact('discount', 'discountPrice'));
    }
}
