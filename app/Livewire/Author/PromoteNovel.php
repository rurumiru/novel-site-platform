<?php
namespace App\Livewire\Author;

use App\Models\Novel;
use App\Models\NovelPromotion;
use App\Models\NovelPromotionPackage;
use App\Models\Transaction;
use Livewire\Component;

class PromoteNovel extends Component {
    public Novel $novel;
    public bool $modalOpen = false;
    public ?int $selectedPackageId = null;
    public string $feedback = '';
    public string $feedbackType = '';

    public function mount(Novel $novel) {
        $this->novel = $novel;
    }

    public function getPackagesProperty() {
        return NovelPromotionPackage::active()->orderBy('price_coins')->get();
    }

    public function getActivePromotionsProperty() {
        return NovelPromotion::active()
            ->where('novel_id', $this->novel->id)
            ->with('package')
            ->get();
    }

    public function purchase(int $packageId) {
        $package = NovelPromotionPackage::find($packageId);
        if (!$package || !$package->is_active) {
            $this->feedback = 'Пакет недоступен.';
            $this->feedbackType = 'error';
            return;
        }

        $user = auth()->user();

        if ($user->balance < $package->price_coins) {
            $this->feedback = 'Недостаточно монет на балансе.';
            $this->feedbackType = 'error';
            return;
        }

        $activeCount = NovelPromotion::active()->where('package_id', $packageId)->count();
        if ($activeCount >= $package->max_slots) {
            $this->feedback = 'Все слоты этого типа заняты. Попробуйте позже.';
            $this->feedbackType = 'error';
            return;
        }

        $alreadyActive = NovelPromotion::active()
            ->where('novel_id', $this->novel->id)
            ->where('type', $package->type)
            ->exists();
        if ($alreadyActive) {
            $this->feedback = 'У этой новеллы уже есть активное продвижение этого типа.';
            $this->feedbackType = 'error';
            return;
        }

        $user->decrement('balance', $package->price_coins);

        Transaction::create([
            'user_id'     => $user->id,
            'type'        => 'spend',
            'amount'      => -$package->price_coins,
            'description' => "Продвижение новеллы «{$this->novel->title}» ({$package->name})",
            'status'      => 'completed',
        ]);

        NovelPromotion::create([
            'novel_id'    => $this->novel->id,
            'user_id'     => $user->id,
            'package_id'  => $package->id,
            'type'        => $package->type,
            'starts_at'   => now(),
            'ends_at'     => now()->addDays($package->duration_days),
            'amount_paid' => $package->price_coins,
            'status'      => 'active',
        ]);

        $this->modalOpen = false;
        $this->feedback = "Новелла продвигается! Активировано на {$package->duration_days} дней.";
        $this->feedbackType = 'success';
    }

    public function render() {
        return view('livewire.author.promote-novel', [
            'packages'         => $this->packages,
            'activePromotions' => $this->activePromotions,
        ]);
    }
}
