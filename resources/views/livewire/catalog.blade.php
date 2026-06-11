<div>
    
    <div class="cat-head">
        <h1>Каталог</h1>
        <p>Полный список новелл на платформе. Уточните жанр, статус, год, автора — найдите свою следующую серию.</p>
        <div class="count-line">
            {{ $novels->total() }} серий · {{ $tags->count() }} тегов · обновляется ежедневно
        </div>
    </div>

    <div class="cat-layout">
        
        <aside class="cat-side">
            
            <div class="cat-filter">
                <h3>Поиск</h3>
                <input type="text"
                       class="eri-input"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Название или автор...">
            </div>

            <div class="cat-filter">
                <h3>Статус</h3>
                @php
                    $statuses = ['ongoing' => 'Выходит', 'completed' => 'Завершён', 'hiatus' => 'Заморожен'];
                @endphp
                @foreach($statuses as $val => $label)
                    <label class="opt">
                        <input type="checkbox"
                               wire:click="setStatus('{{ $val }}')"
                               @checked($status === $val)>
                        <span class="nm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="cat-filter">
                <h3>Год выпуска</h3>
                <input type="number"
                       class="eri-input"
                       wire:model.live.debounce.500ms="year"
                       placeholder="2024">
            </div>

            <div class="cat-filter">
                <h3>Автор оригинала</h3>
                <input type="text"
                       class="eri-input"
                       wire:model.live.debounce.500ms="author"
                       placeholder="Имя автора">
            </div>

            <div class="cat-filter">
                <h3>Страна</h3>
                <input type="text"
                       class="eri-input"
                       wire:model.live.debounce.500ms="country"
                       placeholder="Корея, Китай...">
            </div>

            <div class="cat-filter">
                <h3>
                    Теги и жанры
                    @if(count($selectedTags) > 0)
                        · {{ count($selectedTags) }} выбрано
                    @endif
                </h3>
                @foreach($tags as $tag)
                    <label class="opt">
                        <input type="checkbox"
                               wire:click="toggleTag({{ $tag->id }})"
                               @checked(in_array($tag->id, $selectedTags))>
                        <span class="nm">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>

            <div class="cat-filter-actions">
                <button type="button" wire:click="resetFilters">Сбросить</button>
            </div>
        </aside>

        <main class="cat-main">
            
            <div class="cat-toolbar">
                @php
                    $hasActive = $search || $status || $year || $author || $country || count($selectedTags);
                @endphp

                @if($hasActive)
                    @if($search)
                        <span class="applied-chip">
                            Поиск: {{ $search }}
                            <button type="button" wire:click="$set('search', '')" aria-label="Очистить поиск">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endif

                    @if($status)
                        <span class="applied-chip">
                            {{ $statuses[$status] ?? $status }}
                            <button type="button" wire:click="setStatus('{{ $status }}')" aria-label="Снять статус">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endif

                    @if($year)
                        <span class="applied-chip">
                            Год: {{ $year }}
                            <button type="button" wire:click="$set('year', '')" aria-label="Очистить год">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endif

                    @if($author)
                        <span class="applied-chip">
                            Автор: {{ $author }}
                            <button type="button" wire:click="$set('author', '')" aria-label="Очистить автора">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endif

                    @if($country)
                        <span class="applied-chip">
                            Страна: {{ $country }}
                            <button type="button" wire:click="$set('country', '')" aria-label="Очистить страну">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endif

                    @foreach($tags->whereIn('id', $selectedTags) as $tag)
                        <span class="applied-chip">
                            #{{ $tag->name }}
                            <button type="button" wire:click="toggleTag({{ $tag->id }})" aria-label="Снять тег">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endforeach

                    <button type="button" class="clear-all" wire:click="resetFilters">Сбросить всё</button>
                @else
                    <span style="font-family: var(--mono); font-size: 11px; color: var(--text-muted); letter-spacing: 0.06em; text-transform: uppercase; font-weight: 600;">
                        Фильтры не применены
                    </span>
                @endif

                <span class="spacer"></span>

                <span style="font-family: var(--mono); font-size: 11px; color: var(--text-muted); letter-spacing: 0.06em; text-transform: uppercase; font-weight: 600;">
                    {{ $novels->total() }} результатов
                </span>

                <select wire:model.live="sort">
                    <option value="latest">Самые новые</option>
                    <option value="popular">По популярности</option>
                    <option value="rating">По рейтингу</option>
                </select>
            </div>

            @if($promotedNovels->isNotEmpty())
                <div style="margin-bottom: 24px;">
                    <h3 style="font-family: var(--mono); font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); font-weight: 700; margin: 0 0 12px;">
                        ★ Продвигается
                    </h3>
                    <div class="cat-grid">
                        @foreach($promotedNovels as $novel)
                            <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="cat-card">
                                <div class="cover">
                                    <x-eriiba.cover :novel="$novel" />
                                </div>
                                <div class="chips">
                                    <x-eriiba.chip>Топ</x-eriiba.chip>
                                    @if($novel->status === 'completed')
                                        <x-eriiba.chip>Завершено</x-eriiba.chip>
                                    @endif
                                </div>
                                <h3>{{ $novel->title }}</h3>
                                <div class="author">{{ $novel->author_name ?? '—' }}</div>
                                <div class="meta">
                                    @if(isset($novel->average_rating) && $novel->average_rating)
                                        <span><strong>★ {{ number_format((float)$novel->average_rating, 1) }}</strong></span>
                                        <span>·</span>
                                    @endif
                                    <span>{{ $novel->chapters_count ?? 0 }} гл.</span>
                                    @if(!empty($novel->views))
                                        <span>·</span>
                                        <span>{{ $novel->views }} просм.</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($novels->isEmpty())
                <div class="cat-empty">
                    Ничего не нашлось.
                    <button type="button" wire:click="resetFilters" style="background: none; border: 0; color: var(--accent); font-weight: 600; cursor: pointer; font-family: inherit; font-size: inherit;">Сбросить фильтры</button>
                </div>
            @else
                <div class="cat-grid">
                    @foreach($novels as $novel)
                        @php
                            $genreTag = $novel->relationLoaded('tags') ? $novel->tags->first() : null;
                        @endphp
                        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="cat-card">
                            <div class="cover">
                                <x-eriiba.cover :novel="$novel" />
                            </div>
                            <div class="chips">
                                @if($genreTag)
                                    <x-eriiba.chip>{{ $genreTag->name }}</x-eriiba.chip>
                                @endif
                                @if($novel->status === 'completed')
                                    <x-eriiba.chip>Завершено</x-eriiba.chip>
                                @elseif($novel->status === 'hiatus')
                                    <x-eriiba.chip>Заморожен</x-eriiba.chip>
                                @endif
                            </div>
                            <h3>{{ $novel->title }}</h3>
                            <div class="author">{{ $novel->author_name ?? '—' }}</div>
                            <div class="meta">
                                @if(isset($novel->average_rating) && $novel->average_rating)
                                    <span><strong>★ {{ number_format((float)$novel->average_rating, 1) }}</strong></span>
                                    <span>·</span>
                                @endif
                                <span>{{ $novel->chapters_count ?? 0 }} гл.</span>
                                @if(!empty($novel->views))
                                    <span>·</span>
                                    <span>{{ is_numeric($novel->views) && $novel->views >= 1000 ? round($novel->views / 1000, 1) . 'K' : $novel->views }} просм.</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($novels->hasPages())
                    <div style="margin-top: 32px; display: flex; justify-content: center;">
                        {{ $novels->links() }}
                    </div>
                @endif
            @endif
        </main>
    </div>
</div>
