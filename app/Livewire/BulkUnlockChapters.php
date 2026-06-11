<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkUnlockChapters extends Component
{
    public Novel $novel;
    public array $selected = [];
    public string $error = '';
    public string $success = '';

    public function mount(Novel $novel)
    {
        $this->novel = $novel;
    }

    public function getAvailableChaptersProperty()
    {
        $chapters = $this->novel->publishedChapters()
            ->where('is_locked', true)
            ->where('price', '>', 0)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'price', 'sort_order', 'volume_id']);

        if (!Auth::check() || $chapters->isEmpty()) {
            return $chapters;
        }

        $unlockedIds = DB::table('user_unlocked_chapters')
            ->where('user_id', Auth::id())
            ->whereIn('chapter_id', $chapters->pluck('id'))
            ->pluck('chapter_id')
            ->all();

        return $chapters->reject(fn ($c) => in_array($c->id, $unlockedIds, true))->values();
    }

    private function selectedIds(): array
    {
        return array_values(array_filter(array_map('intval', $this->selected)));
    }

    public function getSelectedTotalProperty(): int
    {
        $ids = $this->selectedIds();
        if (empty($ids)) return 0;
        return (int) $this->availableChapters
            ->whereIn('id', $ids)
            ->sum('price');
    }

    public function getGrandTotalProperty(): int
    {
        return (int) $this->availableChapters->sum('price');
    }

    public function selectAll(): void
    {
        $this->selected = $this->availableChapters->pluck('id')->map(fn ($i) => (int) $i)->all();
        $this->error = $this->success = '';
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->error = $this->success = '';
    }

    public function buy()
    {
        $this->error = $this->success = '';

        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            $this->error = 'Подтвердите почту, чтобы покупать платные главы.';
            return;
        }
        $ids = $this->selectedIds();
        if (empty($ids)) {
            $this->error = 'Выберите хотя бы одну главу.';
            return;
        }

        $chapters = $this->availableChapters->whereIn('id', $ids);
        if ($chapters->isEmpty()) {
            $this->error = 'Выбранные главы недоступны для покупки.';
            return;
        }

        $total = (int) $chapters->sum('price');
        if ($user->balance < $total) {
            $this->error = 'Недостаточно монет. Не хватает ' . ($total - $user->balance) . ' ₽.';
            return;
        }

        $count = $chapters->count();

        DB::transaction(function () use ($user, $chapters, $total) {
            $user->decrement('balance', $total);

            $rows = $chapters->map(fn ($c) => [
                'user_id'    => $user->id,
                'chapter_id' => (int) $c->id,
                'price_paid' => (int) $c->price,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            DB::table('user_unlocked_chapters')->insertOrIgnore($rows);
        });

        $this->selected = [];
        $this->success = 'Куплено глав: ' . $count . ' за ' . $total . ' ₽.';
    }

    public function render()
    {
        $chapters      = $this->availableChapters;
        $grandTotal    = (int) $chapters->sum('price');
        $selectedTotal = (int) $chapters->whereIn('id', $this->selectedIds())->sum('price');

        return view('livewire.bulk-unlock-chapters', [
            'chapters'      => $chapters,
            'grandTotal'    => $grandTotal,
            'selectedTotal' => $selectedTotal,
        ]);
    }
}
