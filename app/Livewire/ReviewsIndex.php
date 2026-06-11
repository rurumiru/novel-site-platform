<?php

namespace App\Livewire;

use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ReviewsIndex extends Component {
    use WithPagination;

    #[Url(as: 'tab')]
    public string $tab = 'all';

    #[Url(as: 'sort')]
    public string $sort = 'latest';

    #[Url(as: 'q')]
    public string $q = '';

    public function updatingTab() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }
    public function updatingQ() { $this->resetPage(); }

    public function render() {
        $pinnedQuery = Review::query()
            ->where('is_published', true)
            ->where('is_pinned', true)
            ->with('user:id,name,username,avatar')
            ->orderByDesc('created_at');

        if (in_array($this->tab, ['review', 'promotion', 'notice'], true)) {
            $pinnedQuery->where('category', $this->tab);
        }

        $pinned = $pinnedQuery->limit(10)->get();

        $listQuery = Review::query()
            ->where('is_published', true)
            ->where('is_pinned', false)
            ->with('user:id,name,username,avatar');

        if (in_array($this->tab, ['review', 'promotion', 'notice'], true)) {
            $listQuery->where('category', $this->tab);
        }

        if (trim($this->q) !== '') {
            $listQuery->where('title', 'like', '%' . trim($this->q) . '%');
        }

        match ($this->sort) {
            'popular'    => $listQuery->orderByDesc('views_count'),
            'recommends' => $listQuery->orderByDesc('recommends_count'),
            default      => $listQuery->orderByDesc('last_activity_at')->orderByDesc('id'),
        };

        $total   = (clone $listQuery)->count();
        $reviews = $listQuery->paginate(20);

        return view('livewire.reviews-index', [
            'pinned'  => $pinned,
            'reviews' => $reviews,
            'total'   => $total,
        ])->extends('layouts.app')->section('content');
    }
}
