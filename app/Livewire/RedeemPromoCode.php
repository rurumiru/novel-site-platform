<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\PromoCode;
use App\Models\Chapter;
use Illuminate\Support\Facades\Auth;

class RedeemPromoCode extends Component {
    public string $code = '';

    public function redeem()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $code  = strtoupper(trim($this->code));
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo) {
            session()->flash('promo_error', 'Промокод не найден.');
            return;
        }

        if (!$promo->isValid()) {
            session()->flash('promo_error', 'Промокод недействителен или истёк.');
            return;
        }

        if (!$promo->canBeUsedBy($user)) {
            session()->flash('promo_error', 'Вы уже использовали этот промокод.');
            return;
        }

        switch ($promo->type) {
            case 'balance':
                $user->increment('balance', $promo->value);
                $promo->usages()->create([
                    'user_id'    => $user->id,
                    'applied_at' => now(),
                ]);
                $promo->increment('uses_count');
                session()->flash('promo_success', "На баланс зачислено {$promo->value} ₽!");
                break;

            case 'discount':
                $promo->usages()->create([
                    'user_id'    => $user->id,
                    'applied_at' => null,
                ]);
                $promo->increment('uses_count');
                $novelName = $promo->novel ? " на «{$promo->novel->title}»" : '';
                session()->flash('promo_success', "Скидка {$promo->value}%{$novelName} активирована! Применится при следующей покупке главы.");
                break;

            case 'free_novel':
                if (!$promo->novel_id || !$promo->novel) {
                    session()->flash('promo_error', 'Промокод настроен неверно (новелла не найдена).');
                    return;
                }

                $lockedChapterIds = Chapter::where('novel_id', $promo->novel_id)
                    ->where('is_locked', true)
                    ->where('is_published', true)
                    ->pluck('id');

                $alreadyUnlocked = $user->unlockedChapters()
                    ->whereIn('chapters.id', $lockedChapterIds)
                    ->pluck('chapters.id');

                $toUnlock = $lockedChapterIds->diff($alreadyUnlocked);

                foreach ($toUnlock as $chapterId) {
                    $user->unlockedChapters()->attach($chapterId, ['price_paid' => 0]);
                }

                $promo->usages()->create([
                    'user_id'    => $user->id,
                    'applied_at' => now(),
                ]);
                $promo->increment('uses_count');

                $novelTitle = $promo->novel->title;
                $count = $toUnlock->count();
                session()->flash('promo_success', "Открыто {$count} глав новеллы «{$novelTitle}»!");
                break;

            case 'free_chapters':
                $limit = $promo->chapters_count ?? 1;

                $query = Chapter::where('is_locked', true)
                    ->where('is_published', true)
                    ->where(function ($q) {
                        $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                    })
                    ->orderBy('sort_order');

                if ($promo->novel_id) {
                    if (!$promo->novel) {
                        session()->flash('promo_error', 'Промокод настроен неверно (новелла не найдена).');
                        return;
                    }
                    $query->where('novel_id', $promo->novel_id);
                }

                $lockedIds = $query->pluck('id');

                $alreadyUnlocked = $user->unlockedChapters()
                    ->whereIn('chapters.id', $lockedIds)
                    ->pluck('chapters.id');

                $toUnlock = $lockedIds->diff($alreadyUnlocked)->take($limit);

                if ($toUnlock->isEmpty()) {
                    session()->flash('promo_error', 'Нет доступных глав для открытия — все уже открыты.');
                    return;
                }

                foreach ($toUnlock as $chapterId) {
                    $user->unlockedChapters()->attach($chapterId, ['price_paid' => 0]);
                }

                $promo->usages()->create([
                    'user_id'    => $user->id,
                    'applied_at' => now(),
                ]);
                $promo->increment('uses_count');

                $count = $toUnlock->count();
                $novelLabel = $promo->novel ? " новеллы «{$promo->novel->title}»" : '';
                session()->flash('promo_success', "Открыто {$count} глав{$novelLabel}!");
                break;
        }

        $this->code = '';
    }

    public function render()
    {
        $activeDiscounts = [];
        if (Auth::check()) {
            $activeDiscounts = \App\Models\PromoCodeUsage::where('user_id', Auth::id())
                ->whereNull('applied_at')
                ->whereHas('promoCode', fn($q) => $q->where('type', 'discount')->where('is_active', true))
                ->with('promoCode.novel')
                ->get();
        }

        return view('livewire.redeem-promo-code', compact('activeDiscounts'));
    }
}
