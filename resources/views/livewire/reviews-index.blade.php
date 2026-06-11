<div class="eri-section reviews-page">
    <x-eriiba.section-head
        title="Обзоры работ"
        sub="Рецензии, рекомендации и продвижение работ от читателей сообщества." />

    <div class="reviews-tabs">
        <button type="button" wire:click="$set('tab','all')"        class="reviews-tab {{ $tab==='all'?'on':'' }}">Полный</button>
        <button type="button" wire:click="$set('tab','review')"    class="reviews-tab {{ $tab==='review'?'on':'' }}">Обзоры работ</button>
        <button type="button" wire:click="$set('tab','promotion')" class="reviews-tab {{ $tab==='promotion'?'on':'' }}">Продвижение работ</button>
    </div>

    <div class="reviews-toolbar" x-data="{ sortOpen: false }" @click.outside="sortOpen = false">
        <div class="reviews-total">
            Всего: <strong>{{ $total }}</strong> {{ trans_choice('обзор|обзора|обзоров', $total) }}
        </div>

        <div class="reviews-toolbar-right">
            
            <div class="reviews-sort-seg" role="tablist" aria-label="Сортировка">
                <button type="button" wire:click="$set('sort','latest')"     class="reviews-sort-btn {{ $sort==='latest'    ? 'on' : '' }}">
                    <i class="fa-solid fa-clock"></i> Свежие
                </button>
                <button type="button" wire:click="$set('sort','popular')"    class="reviews-sort-btn {{ $sort==='popular'   ? 'on' : '' }}">
                    <i class="fa-solid fa-eye"></i> Популярные
                </button>
                <button type="button" wire:click="$set('sort','recommends')" class="reviews-sort-btn {{ $sort==='recommends'? 'on' : '' }}">
                    <i class="fa-solid fa-thumbs-up"></i> Лучшие
                </button>
            </div>

            <label class="reviews-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" wire:model.live.debounce.400ms="q" placeholder="Поиск по заголовку…" class="reviews-search">
            </label>
        </div>
    </div>

    <div class="reviews-table-wrap">
        <table class="reviews-table">
            <thead>
                <tr>
                    <th class="col-num">№</th>
                    <th class="col-title">Название статьи</th>
                    <th class="col-author">Автор</th>
                    <th class="col-num-cell">Запросов</th>
                    <th class="col-num-cell">Реком.</th>
                    <th class="col-time">Время</th>
                </tr>
            </thead>
            <tbody>
                
                @foreach($pinned as $r)
                    <tr class="row-pinned">
                        <td class="col-num"><i class="fa-solid fa-thumbtack" aria-hidden="true"></i></td>
                        <td class="col-title">
                            <span class="badge-notice">УВЕДОМЛЕНИЕ</span>
                            <a href="{{ route('reviews.show', $r->id) }}" wire:navigate class="reviews-link">{{ $r->title }}</a>
                            @if($r->comments_count)<span class="comments-badge">{{ $r->comments_count }}</span>@endif
                        </td>
                        <td class="col-author">{{ $r->user?->name ?? '—' }}</td>
                        <td class="col-num-cell">{{ $r->views_count }}</td>
                        <td class="col-num-cell">{{ $r->recommends_count }}</td>
                        <td class="col-time">{{ optional($r->last_activity_at ?? $r->created_at)->format('y.m.d') }}</td>
                    </tr>
                @endforeach

                @php $startNum = $total - (($reviews->currentPage() - 1) * $reviews->perPage()); @endphp
                @forelse($reviews as $i => $r)
                    <tr>
                        <td class="col-num">{{ $startNum - $i }}</td>
                        <td class="col-title">
                            <span class="badge-cat badge-cat-{{ $r->category }}">[{{ $r->category_label }}]</span>
                            <a href="{{ route('reviews.show', $r->id) }}" wire:navigate class="reviews-link">{{ $r->title }}</a>
                            @if($r->comments_count)<span class="comments-badge">{{ $r->comments_count }}</span>@endif
                        </td>
                        <td class="col-author">{{ $r->user?->name ?? '—' }}</td>
                        <td class="col-num-cell">{{ $r->views_count }}</td>
                        <td class="col-num-cell">{{ $r->recommends_count }}</td>
                        <td class="col-time">{{ optional($r->last_activity_at ?? $r->created_at)->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="reviews-empty">Пока нет обзоров. Будьте первым!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="reviews-foot">
        <div class="reviews-pagination">{{ $reviews->onEachSide(2)->links() }}</div>
        @auth
            <a href="{{ route('reviews.create') }}" wire:navigate class="eri-btn primary reviews-write-btn">
                <i class="fa-solid fa-pen"></i> Писать
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate class="eri-btn">
                <i class="fa-solid fa-right-to-bracket"></i> Войдите, чтобы написать
            </a>
        @endauth
    </div>

    @if($reviews->total() > 0)
        <div class="reviews-meta-bottom">
            {{ $reviews->currentPage() }} страница из {{ $reviews->lastPage() }} {{ trans_choice('страницы|страниц|страниц', $reviews->lastPage()) }}
            (всего {{ $reviews->total() }})
        </div>
    @endif
</div>
