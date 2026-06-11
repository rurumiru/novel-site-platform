<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Novel;
use App\Models\Donation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DonateButton extends Component {
    public Novel $novel;
    public bool $open = false;
    public int $amount = 100;
    public string $message = '';
    public bool $isAnonymous = false;
    public ?string $error = null;

    protected $rules = [
        'amount' => 'required|integer|min:10|max:100000',
        'message' => 'nullable|string|max:280',
    ];

    protected $messages = [
        'amount.min' => 'Минимум 10 ₽',
        'amount.max' => 'Максимум 100 000 ₽',
        'message.max' => 'Сообщение до 280 символов',
    ];

    public function mount(Novel $novel) {
        $this->novel = $novel;
    }

    public function setAmount(int $amount) {
        $this->amount = $amount;
    }

    public function donate() {
        $this->error = null;

        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->validate();

        $user = Auth::user();
        if ($user->id === $this->novel->user_id) {
            $this->error = 'Нельзя задонатить самому себе.';
            return;
        }

        if (($user->balance ?? 0) < $this->amount) {
            $this->error = 'Недостаточно средств на балансе. Пополните баланс на странице профиля.';
            return;
        }

        DB::transaction(function () use ($user) {
            $user->decrement('balance', $this->amount);
            if ($author = $this->novel->publisher) {
                $author->increment('balance', $this->amount);
            }
            Donation::create([
                'user_id'      => $user->id,
                'novel_id'     => $this->novel->id,
                'author_id'    => $this->novel->user_id,
                'amount'       => $this->amount,
                'message'      => trim($this->message) ?: null,
                'is_anonymous' => $this->isAnonymous,
                'status'       => 'completed',
            ]);
        });

        try {
            \App\Services\TrustLevelService::recalculateFor($user);
        } catch (\Throwable $e) {  }

        $donatedAmount = $this->amount;

        $this->open = false;
        $this->message = '';
        $this->amount = 100;
        $this->dispatch('donation-completed');
        session()->flash('donation_ok', 'Спасибо за поддержку! Автор получил ' . number_format($donatedAmount, 0, '.', ' ') . ' ₽.');
    }

    public function render() {
        return view('livewire.donate-button');
    }
}
