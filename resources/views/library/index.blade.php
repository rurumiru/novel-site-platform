@extends('layouts.app')
@section('title', 'Моя библиотека')

@section('content')
@php
    $shelves = [
        ['id' => 'reading',   'label' => 'Читаю',          'items' => $reading],
        ['id' => 'finished',  'label' => 'Прочитано',      'items' => $finished],
        ['id' => 'paused',    'label' => 'Приостановлено', 'items' => $paused],
        ['id' => 'favorites', 'label' => 'Избранное',      'items' => $favorites],
        ['id' => 'bookmarks', 'label' => 'Закладки',       'items' => $bookmarks ?? collect()],
        ['id' => 'beta',      'label' => 'Бета-чтение',    'items' => $betaNovels],
    ];

    $tabsJson = collect($shelves)->map(fn($s) => [
        'id' => $s['id'], 'label' => $s['label'], 'count' => $s['items']->count(),
    ])->values()->toJson(JSON_UNESCAPED_UNICODE);

    $defaultTab = collect($shelves)->first(fn($s) => $s['items']->isNotEmpty())['id'] ?? 'reading';
@endphp

<div class="eri-page" x-data="{ tab: '{{ $defaultTab }}', view: 'list' }">

    <div class="lib-head">
        <h1>Моя библиотека</h1>
        <p>Полки, обновления и прогресс по каждой серии. Здесь — всё, что вы читали, отметили или ждёте от автора.</p>

        <div class="lib-tabs">
            @foreach($shelves as $shelf)
                <button class="lib-tab"
                        :class="tab === '{{ $shelf['id'] }}' ? 'on' : ''"
                        @click="tab = '{{ $shelf['id'] }}'">
                    {{ $shelf['label'] }} <span class="count">{{ $shelf['items']->count() }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="lib-toolbar">
        <span style="font-family: var(--mono); font-size: 11px; color: var(--text-muted); letter-spacing: 0.06em; text-transform: uppercase; font-weight: 600;">
            Вид
        </span>
        <div class="view-toggle">
            <button :class="view === 'list' ? 'on' : ''" @click="view = 'list'" title="Список">
                <i class="fa-solid fa-list"></i>
            </button>
            <button :class="view === 'grid' ? 'on' : ''" @click="view = 'grid'" title="Сетка">
                <i class="fa-solid fa-table-cells-large"></i>
            </button>
        </div>
        <span class="spacer"></span>
        <a href="{{ route('catalog') }}" wire:navigate class="filter-pill">
            <i class="fa-solid fa-magnifying-glass"></i> Каталог
        </a>
        <a href="{{ route('updates') }}" wire:navigate class="filter-pill">
            <i class="fa-solid fa-bolt"></i> Обновления
        </a>
    </div>

    <div class="lib-body">
        @foreach($shelves as $shelf)
            <div x-show="tab === '{{ $shelf['id'] }}'" x-transition style="display:none;">
                @if($shelf['items']->isEmpty())
                    <div class="lib-empty">
                        Полка пуста.
                        <a href="{{ route('catalog') }}" wire:navigate style="color: var(--accent); font-weight: 600;">
                            Посмотреть каталог →
                        </a>
                    </div>
                @else
                    
                    <div class="lib-list" x-show="view === 'list'">
                        @foreach($shelf['items'] as $item)
                            @php
                                $isProgress  = in_array($shelf['id'], ['reading', 'finished', 'paused']);
                                $isBookmark  = $shelf['id'] === 'bookmarks';
                                $novel       = $isProgress ? $item->novel : ($isBookmark ? $item->chapter->novel : $item);
                                $percent     = $isProgress ? ($item->lib_percent ?? 0) : 0;
                                $progressClass = $shelf['id'] === 'finished' ? 'done' : ($shelf['id'] === 'paused' ? 'paused' : '');

                                $chapterTitle  = $isProgress && $item->chapter ? $item->chapter->title : null;
                                $chapterOrder  = $isProgress ? ($item->lib_chapter ?? 0) : ($isBookmark ? ($item->chapter->sort_order ?? 0) : 0);
                                $chapterHref   = null;
                                if ($isProgress && $item->chapter_id) {
                                    $chapterHref = route('novel.read', [$novel->id, $item->chapter_id]);
                                } elseif ($isBookmark) {
                                    $chapterHref = route('novel.read', [$novel->id, $item->chapter_id]);
                                    $chapterTitle = $item->chapter->title ?? null;
                                }
                                $volumeTitle   = $isBookmark ? ($item->chapter->volume->title ?? null) : null;

                                $genreLabel    = optional($novel->genres?->first())->name ?? '—';
                                $totalChapters = $novel->chapters_count ?? 0;
                                $href          = route('novel.show', $novel->id);
                                $readHref      = $chapterHref ?? $href;
                                $updatedAt     = $isProgress ? $item->updated_at : ($isBookmark ? $item->created_at : null);
                            @endphp
                            <div class="lib-row">
                                <a href="{{ $href }}" wire:navigate>
                                    <x-eriiba.cover :novel="$novel" />
                                </a>
                                <div class="lib-row-title">
                                    <h3><a href="{{ $href }}" wire:navigate style="color:inherit;text-decoration:none;">{{ $novel->title }}</a></h3>
                                    <div class="author">{{ $novel->publisher->name ?? $novel->author ?? '' }}</div>
                                    <div class="meta">
                                        <span>{{ $genreLabel }}</span>
                                        @if($totalChapters)<span>·</span><span>{{ $totalChapters }} гл.</span>@endif
                                    </div>
                                    
                                    @if($chapterTitle)
                                        <div class="lib-chapter-name">
                                            @if($volumeTitle)
                                                <span class="lib-vol-tag">{{ $volumeTitle }}</span>
                                            @endif
                                            <a href="{{ $readHref }}" wire:navigate style="color:var(--accent);font-size:12px;font-weight:500;text-decoration:none;">
                                                <i class="fa-solid fa-book-open-reader" style="font-size:10px;margin-right:4px;"></i>
                                                {{ Str::limit($chapterTitle, 60) }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($isProgress)
                                <div class="lib-progress {{ $progressClass }}">
                                    <div class="bar"><i style="width: {{ $percent }}%"></i></div>
                                    <div class="info">
                                        <span>
                                            @if($percent === 0) Не начато
                                            @elseif($percent >= 100) Завершено
                                            @else Гл. {{ $chapterOrder }} из {{ $totalChapters }}
                                            @endif
                                        </span>
                                        <span>{{ $percent }}%</span>
                                    </div>
                                </div>
                                @elseif($isBookmark)
                                
                                <div class="lib-progress">
                                    <div style="display:flex;align-items:center;gap:6px;padding:6px 0;">
                                        <i class="fa-solid fa-bookmark" style="color:var(--accent);font-size:14px;"></i>
                                        <span style="font-size:12px;color:var(--text-muted);">Закладка на гл. {{ $chapterOrder }}</span>
                                    </div>
                                </div>
                                @else
                                <div class="lib-progress">
                                    <div class="info">
                                        <span>
                                            @if($shelf['id'] === 'favorites') ★ В избранном
                                            @elseif($shelf['id'] === 'beta') Бета-чтение
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <div class="lib-update">
                                    @if($updatedAt)
                                        <div class="when">{{ $updatedAt->diffForHumans(null, true, true) }}</div>
                                        <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">{{ $updatedAt->format('d.m.Y') }}</div>
                                    @elseif($shelf['id'] === 'favorites')
                                        <div class="badge">★ избранное</div>
                                    @elseif($shelf['id'] === 'beta')
                                        <div class="badge">бета</div>
                                    @endif
                                </div>

                                <div class="row-act">
                                    <a href="{{ $readHref }}" wire:navigate title="{{ $isBookmark ? 'Перейти к закладке' : 'Продолжить' }}"
                                       style="width:32px;height:32px;background:none;border:1px solid transparent;border-radius:var(--r-sm);display:grid;place-items:center;color:var(--text-3);text-decoration:none;">
                                        <i class="fa-solid {{ $isBookmark ? 'fa-bookmark' : 'fa-play' }}" style="font-size:11px;"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="lib-grid" x-show="view === 'grid'" style="display:none;">
                        @foreach($shelf['items'] as $item)
                            @php
                                $isProgress = $shelf['id'] === 'reading' || $shelf['id'] === 'finished' || $shelf['id'] === 'paused';
                                $novel    = $isProgress ? $item->novel : $item;
                                $percent  = $isProgress ? ($item->lib_percent ?? 0) : 0;
                                $chapter  = $isProgress ? ($item->lib_chapter ?? 0) : 0;
                                $href = route('novel.show', $novel->id);
                            @endphp
                            <a href="{{ $href }}" wire:navigate class="lib-card">
                                <x-eriiba.cover :novel="$novel" />
                                <div class="progress-mini"><i style="width: {{ $percent }}%"></i></div>
                                <h3>{{ $novel->title }}</h3>
                                <div class="author">{{ $novel->publisher->name ?? $novel->author ?? '' }}</div>
                                <div class="pct">
                                    @if($shelf['id'] === 'favorites') ★ Избранное
                                    @elseif($shelf['id'] === 'beta') Бета
                                    @elseif($percent >= 100) Завершено
                                    @elseif($percent === 0) Не начато
                                    @else гл. {{ $chapter }} · {{ $percent }}%
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
