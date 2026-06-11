@extends('layouts.app')
@section('title', $chapter->title . ' — ' . $novel->title)

@section('hideNav', '1')
@section('hideFooter', '1')

@php
    $prevCh = $chapter->prevChapter();
    $nextCh = $chapter->nextChapter();

    $allChapters = collect();
    foreach ($volumes as $vol) {
        foreach ($vol->chapters as $c) $allChapters->push($c);
    }
    foreach ($chaptersWithoutVolume as $c) $allChapters->push($c);
    $totalCh = $allChapters->count();
    $curIdx  = $allChapters->search(fn($c) => $c->id === $chapter->id);
    $curPos  = $curIdx === false ? 1 : ($curIdx + 1);

    $words   = (int) round(mb_strlen(strip_tags($chapter->content ?? '')) / 5.5);
    $readMin = max(1, (int) round($words / 200));
    $when    = ($chapter->published_at ?? $chapter->created_at);
@endphp

@push('scripts')
@if($prevCh)<link rel="prefetch" href="{{ route('novel.read', [$novel->id, $prevCh->id]) }}">@endif
@if($nextCh)<link rel="prefetch" href="{{ route('novel.read', [$novel->id, $nextCh->id]) }}">@endif

<script>
(function(){
    var release = function(){
        document.documentElement.classList.add('no-reader');
        document.documentElement.classList.remove('h-full');
        document.body.style.overflow = 'visible';
        document.body.style.height = 'auto';
    };
    release();
    document.addEventListener('livewire:navigated', release);
})();
</script>
@endpush

@section('content')

<div id="reader-scroll-area"
     class="page reader-v1"
     x-data="readerData()"
     x-init="init()"
     x-bind:class="immersive ? 'reader-immersive-on' : ''">

    <header class="reader-mobile-head">
        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="reader-mhead-back" title="К новелле">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="reader-mhead-title">
            <div class="reader-mhead-novel">{{ $novel->title }}</div>
            <div class="reader-mhead-chapter">Глава {{ $chapter->sort_order }}: {{ $chapter->title }}</div>
        </div>
        <button type="button" class="reader-mhead-menu" @click="chaptersOpen = true" title="Содержание">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
        </button>
    </header>

    <div class="reader-shell" x-bind:class="immersive ? 'reader-shell-noside' : ''">

        <aside class="reader-side" x-show="!immersive">
            <div class="reader-side-head">
                <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="reader-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    К новелле
                </a>
                <div class="reader-side-title display">{{ $novel->title }}</div>
                <div class="reader-side-author">{{ $novel->publisher?->name ?? '' }}</div>
            </div>

            <div class="reader-side-progress">
                <div class="reader-side-progress-bar">
                    <div class="reader-side-progress-fill" style="width: {{ $totalCh > 0 ? round($curPos / $totalCh * 100) : 0 }}%"></div>
                </div>
                <div class="reader-side-progress-meta">
                    <span>Гл. {{ $curPos }} из {{ $totalCh }}</span>
                    <span>{{ $totalCh > 0 ? round($curPos / $totalCh * 100) : 0 }}%</span>
                </div>
            </div>

            <div class="reader-side-search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" placeholder="Поиск по главам" x-model="tocQuery">
            </div>

            <div class="reader-toc">
                @foreach($volumes as $volIdx => $vol)
                    <div class="toc-vol-head">
                        {{ $vol->title }}
                    </div>
                    @foreach($vol->chapters as $vChIdx => $ch)
                        @php
                            $isCur  = $ch->id === $chapter->id;
                            $isRead = $ch->sort_order < $chapter->sort_order;
                            $localNum = $vChIdx + 1;
                        @endphp
                        <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                           class="toc-row {{ $isCur ? 'cur' : '' }} {{ $isRead ? 'read' : '' }}"
                           x-show="tocQuery === '' || '{{ \Illuminate\Support\Str::lower(addslashes($ch->title)) }}'.includes(tocQuery.toLowerCase())">
                            <span class="toc-num">{{ $localNum }}</span>
                            <span class="toc-title">{{ $ch->title }}</span>
                            @if($ch->is_locked && !$ch->is_unlocked)
                                <span class="toc-lock">🔒</span>
                            @elseif($isRead)
                                <span class="toc-check">✓</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach

                @if($chaptersWithoutVolume->count() > 0)
                    @if($volumes->count() > 0)
                        <div class="toc-vol-head">Без тома</div>
                    @endif
                    @foreach($chaptersWithoutVolume as $cIdx => $ch)
                        @php
                            $isCur  = $ch->id === $chapter->id;
                            $isRead = $ch->sort_order < $chapter->sort_order;
                            $localNum = $cIdx + 1;
                        @endphp
                        <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                           class="toc-row {{ $isCur ? 'cur' : '' }} {{ $isRead ? 'read' : '' }}"
                           x-show="tocQuery === '' || '{{ \Illuminate\Support\Str::lower(addslashes($ch->title)) }}'.includes(tocQuery.toLowerCase())">
                            <span class="toc-num">{{ $localNum }}</span>
                            <span class="toc-title">{{ $ch->title }}</span>
                            @if($ch->is_locked && !$ch->is_unlocked)
                                <span class="toc-lock">🔒</span>
                            @elseif($isRead)
                                <span class="toc-check">✓</span>
                            @endif
                        </a>
                    @endforeach
                @endif
            </div>
        </aside>

        <main class="reader-paper-wrap">
            <div class="reader-paper" :style="'--reader-fs:' + fontSize + 'px; max-width:' + readerWidth + 'px;'">

                <nav class="reader-crumb">
                    <a href="{{ route('home') }}" wire:navigate>Главная</a>
                    <span>›</span>
                    <a href="{{ route('novel.show', $novel->id) }}" wire:navigate>{{ $novel->title }}</a>
                    <span>›</span>
                    <span>Глава {{ $chapter->sort_order }}</span>
                </nav>

                <header class="reader-h">
                    <div class="reader-h-num">Глава {{ $chapter->sort_order }}</div>
                    <h1 class="display reader-h-title">{{ $chapter->title }}</h1>
                    <div class="reader-h-meta">
                        <span>≈ {{ $readMin }} мин · {{ number_format($words) }} слов</span>
                        @if($when)
                            <span>·</span>
                            <span>{{ $when->diffForHumans() }}</span>
                        @endif
                        @if($isBetaReader)
                            <span>·</span>
                            <span style="color:var(--accent);font-weight:600;"><i class="fa-solid fa-pencil"></i> Бета</span>
                        @endif
                    </div>
                </header>

                <article class="reader-body chapter-content"
                         id="chapters-container"
                         data-chapter-id="{{ $chapter->id }}"
                         :style="'font-size:' + fontSize + 'px;'">
                    {!! \App\Services\ContentRenderer::toHtml($chapter->content ?? '') !!}
                    <hr class="reader-rule">
                    <p class="reader-end">— конец главы —</p>
                </article>

                <div class="reader-react">
                    
                    @livewire('chapter-like', ['chapter' => $chapter])

                    <button class="reader-react-btn" @click="commentsOpen = true">
                        💬 <span x-text="commentsCount"></span> комментариев
                    </button>

                    @auth
                        @if($isBetaReader)
                            <button class="reader-react-btn" @click="betaNoteOpen = true; betaNoteContent=''; betaNoteSuccess=false"
                                    style="color:var(--accent);">
                                ✎ Заметка бета-ридера
                            </button>
                        @endif
                    @endauth
                </div>

                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:32px;padding:16px 0;border-bottom:1px solid var(--paper-rule);">
                    <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Поделиться главой</span>
                    <x-share-buttons :url="route('novel.read', [$novel->id, $chapter->id])"
                                     :title="$chapter->title . ' — ' . $novel->title"
                                     size="sm" />
                </div>

                @php $chapterImages = \App\Services\ContentRenderer::extractImages(\App\Services\ContentRenderer::toHtml($chapter->content ?? '')); @endphp
                @if(count($chapterImages) > 0)
                    <div style="margin-bottom:32px;padding-top:24px;border-top:1px solid var(--paper-rule);">
                        <h3 style="font-family:var(--display);font-size:18px;font-weight:500;margin:0 0 12px;color:var(--text);letter-spacing:-0.01em;">
                            <i class="fa-solid fa-images" style="color:var(--accent);margin-right:6px;"></i>
                            Иллюстрации главы ({{ count($chapterImages) }})
                        </h3>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
                            @foreach($chapterImages as $i => $imgSrc)
                                <a href="#" @click.prevent="lightboxSrc='{{ $imgSrc }}'; lightboxOpen=true"
                                   style="display:block;aspect-ratio:1;border-radius:var(--r-sm);overflow:hidden;border:1px solid var(--border);">
                                    <img src="{{ $imgSrc }}" alt="Иллюстрация {{ $i + 1 }}"
                                         loading="{{ $i < 6 ? 'eager' : 'lazy' }}" decoding="async"
                                         style="width:100%;height:100%;object-fit:cover;">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="reader-nav">
                    @if($prevCh)
                        <a href="{{ route('novel.read', [$novel->id, $prevCh->id]) }}" wire:navigate class="reader-nav-btn prev">
                            <span class="reader-nav-lbl">← Предыдущая</span>
                            <span class="reader-nav-title">Глава {{ $prevCh->sort_order }}: {{ $prevCh->title }}</span>
                        </a>
                    @else
                        <span class="reader-nav-btn prev" style="opacity:0.4;cursor:default;">
                            <span class="reader-nav-lbl">← Предыдущая</span>
                            <span class="reader-nav-title" style="color:var(--text-muted);">Это первая глава</span>
                        </span>
                    @endif

                    @if($nextCh)
                        <a href="{{ route('novel.read', [$novel->id, $nextCh->id]) }}" wire:navigate class="reader-nav-btn next">
                            <span class="reader-nav-lbl">Следующая →</span>
                            <span class="reader-nav-title">Глава {{ $nextCh->sort_order }}: {{ $nextCh->title }}</span>
                        </a>
                    @else
                        <span class="reader-nav-btn next" style="opacity:0.4;cursor:default;">
                            <span class="reader-nav-lbl">Следующая →</span>
                            <span class="reader-nav-title" style="color:var(--text-muted);">Это последняя глава</span>
                        </span>
                    @endif
                </div>
            </div>
        </main>

        <div class="reader-tools">

            <a class="tool-btn" href="{{ route('novel.show', $novel->id) }}" wire:navigate title="К странице новеллы">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </a>
            @if($prevCh)
                <a class="tool-btn" href="{{ route('novel.read', [$novel->id, $prevCh->id]) }}" wire:navigate title="Предыдущая глава">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @else
                <span class="tool-btn" style="opacity:0.3;cursor:default;" title="Это первая глава">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            @endif
            @if($nextCh)
                <a class="tool-btn" href="{{ route('novel.read', [$novel->id, $nextCh->id]) }}" wire:navigate title="Следующая глава">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span class="tool-btn" style="opacity:0.3;cursor:default;" title="Это последняя глава">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            @endif

            <div class="tool-spacer"></div>

            <button class="tool-btn" title="Меньше шрифт" @click="fontSize = Math.max(14, fontSize - 2)">A−</button>
            <button class="tool-btn" title="Больше шрифт" @click="fontSize = Math.min(32, fontSize + 2)">A+</button>
            <button class="tool-btn" title="Тема (light/sepia/dark)" @click="cycleTheme()">
                <span x-show="currentTheme === 'light'">☀</span>
                <span x-show="currentTheme === 'dark'" x-cloak>☾</span>
                <span x-show="currentTheme === 'sepia'" x-cloak>✦</span>
            </button>
            <button class="tool-btn" title="Ширина" @click="cycleWidth()">⇔</button>

            <div class="tool-spacer"></div>

            <button class="tool-btn" title="Комментарии" @click="commentsOpen = true" style="position:relative;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span x-show="commentsCount > 0" x-text="commentsCount > 99 ? '99+' : commentsCount" x-cloak
                      style="position:absolute;top:-4px;right:-4px;min-width:16px;height:16px;padding:0 4px;
                             background:var(--accent);color:#fff;border-radius:999px;
                             font-size:9px;font-weight:700;display:grid;place-items:center;
                             border:2px solid var(--surface);font-family:var(--sans);"></span>
            </button>

            @auth
                <button class="tool-btn" title="Закладка на эту главу"
                        @click="toggleBookmark()"
                        x-bind:style="bookmarked ? 'color: var(--accent);' : ''">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                </button>
                <button class="tool-btn" title="Пожаловаться на главу" @click="reportOpen = true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                </button>
            @endauth

            <div class="tool-spacer"></div>

            <button class="tool-btn"
                    @click="toggleImmersive()"
                    x-bind:title="immersive ? 'Показать сайдбар (содержание, прогресс)' : 'Скрыть сайдбар'"
                    x-bind:style="!immersive ? 'color: var(--accent);' : ''">
                
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                    <line x1="9" y1="4" x2="9" y2="20"/>
                </svg>
            </button>
            <button class="tool-btn" title="На весь экран" @click="toggleFullscreen()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
            </button>

            <div class="tool-spacer"></div>

            <button class="tool-btn" title="Содержание" @click="chaptersOpen = true">≡</button>
            <button class="tool-btn" title="Наверх" @click="window.scrollTo({top:0, behavior:'smooth'})">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
            </button>
        </div>
    </div>

    <template x-teleport="body">
    <div x-show="chaptersOpen" x-cloak
         class="eri-modal-overlay"
         @click.self="chaptersOpen = false">
        <div style="background:var(--surface);width:100%;max-width:720px;max-height:85vh;border-radius:var(--r-lg);padding:24px;overflow-y:auto;box-shadow:var(--shadow-lg);" @click.stop>
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px;">
                <span style="font-family:var(--display);font-size:18px;font-weight:500;">Содержание</span>
                <button @click="chaptersOpen = false" style="background:none;border:0;font-size:24px;color:var(--text-3);cursor:pointer;">×</button>
            </div>
            @foreach($volumes as $volIdx => $vol)
                <div style="font-family:var(--display);font-size:13px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin:14px 0 6px;">
                    {{ $vol->title }}
                </div>
                @foreach($vol->chapters as $vChIdx => $ch)
                    <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                       class="toc-row {{ $ch->id === $chapter->id ? 'cur' : '' }}">
                        <span class="toc-num">{{ $vChIdx + 1 }}</span>
                        <span class="toc-title">{{ $ch->title }}</span>
                        @if($ch->is_locked && !$ch->is_unlocked)<span class="toc-lock">🔒</span>@endif
                    </a>
                @endforeach
            @endforeach
            @if($chaptersWithoutVolume->count() > 0)
                @if($volumes->count() > 0)
                    <div style="font-family:var(--display);font-size:13px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin:14px 0 6px;">Без тома</div>
                @endif
                @foreach($chaptersWithoutVolume as $cIdx => $ch)
                    <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                       class="toc-row {{ $ch->id === $chapter->id ? 'cur' : '' }}">
                        <span class="toc-num">{{ $cIdx + 1 }}</span>
                        <span class="toc-title">{{ $ch->title }}</span>
                        @if($ch->is_locked && !$ch->is_unlocked)<span class="toc-lock">🔒</span>@endif
                    </a>
                @endforeach
            @endif
        </div>
    </div>
    </template>

    <template x-teleport="body">
    <div x-show="commentsOpen" x-cloak
         x-init="$watch('commentsOpen', v => v && loadComments())"
         class="eri-modal-overlay"
         @click.self="commentsOpen = false">
        <div style="background:var(--surface);width:100%;max-width:760px;max-height:85vh;border-radius:var(--r-lg);padding:24px;overflow-y:auto;display:flex;flex-direction:column;box-shadow:var(--shadow-lg);" @click.stop>
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:16px;">
                <span style="font-family:var(--display);font-size:18px;font-weight:500;">
                    Комментарии <span x-show="commentsCount" x-text="'(' + commentsCount + ')'" style="color:var(--text-muted);font-weight:400;"></span>
                </span>
                <button @click="commentsOpen = false" style="background:none;border:0;font-size:24px;color:var(--text-3);cursor:pointer;">×</button>
            </div>

            <div id="comments-container" style="flex:1;overflow-y:auto;min-height:0;">
                <template x-if="commentsLoading">
                    <div style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;"></i>
                    </div>
                </template>
                <template x-if="!commentsLoading && commentsError">
                    <div style="text-align:center;padding:40px;color:var(--err);" x-text="commentsError"></div>
                </template>
                <template x-if="!commentsLoading && !commentsError">
                    <div>
                        @auth
                            <div class="eri-card" style="margin-bottom:18px;">
                                <textarea x-model="newCommentContent" id="comment-textarea" rows="3"
                                          placeholder="Напишите ваше мнение... (ссылки запрещены)"
                                          class="eri-textarea" style="margin-bottom:10px;"></textarea>
                                <div style="display:flex;justify-content:flex-end;">
                                    <button @click="postComment()"
                                            :disabled="postingComment || !newCommentContent.trim()"
                                            class="eri-btn primary sm">Отправить</button>
                                </div>
                            </div>
                        @else
                            <div class="eri-alert" style="text-align:center;margin-bottom:18px;">
                                <a href="{{ route('login') }}" wire:navigate style="color:var(--accent);font-weight:600;">Войдите</a>,
                                чтобы оставить комментарий.
                            </div>
                        @endauth
                        <div id="comments-list" style="display:flex;flex-direction:column;gap:18px;"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    </template>

    @auth
    @if($isBetaReader)
        <template x-teleport="body">
        <div x-show="betaNoteOpen" x-cloak
             class="eri-modal-overlay"
             @click.self="betaNoteOpen = false">
            <div class="eri-card" style="width:100%;max-width:480px;" @click.stop>
                <h3 style="font-family:var(--display);font-size:18px;margin:0 0 12px;">
                    <i class="fa-solid fa-pencil" style="color:var(--accent);"></i> Заметка бета-ридера
                </h3>
                <label class="eri-label">Замечание / идея / отзыв</label>
                <textarea x-model="betaNoteContent" rows="5"
                          placeholder="Что подметили в этой главе?"
                          class="eri-textarea" style="margin-bottom:12px;"></textarea>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <x-eriiba.btn variant="ghost" size="sm" x-on:click="betaNoteOpen = false">Отмена</x-eriiba.btn>
                    <button class="eri-btn primary sm"
                            @click="submitBetaNote()"
                            :disabled="betaNoteSubmitting || !betaNoteContent.trim()">
                        <span x-show="!betaNoteSubmitting">Отправить</span>
                        <span x-show="betaNoteSubmitting" x-cloak><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
                <p x-show="betaNoteSuccess" x-cloak style="text-align:center;color:var(--ok);font-weight:600;margin-top:10px;">Спасибо!</p>
            </div>
        </div>
        </template>
    @endif
    @endauth

    @auth
        <template x-teleport="body">
        <div x-show="errorBubbleVisible" x-cloak
             :style="'position:fixed;top:'+errorBubbleY+'px;left:'+errorBubbleX+'px;transform:translate(-50%,-110%);z-index:1000;'"
             data-error-ui>
            <button class="eri-btn sm" style="background:var(--warn);color:#fff;border-color:var(--warn);"
                    @click.stop="openErrorModal()">
                <i class="fa-solid fa-flag" style="font-size:10px;"></i> Ошибка
            </button>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="errorModalOpen" x-cloak
             class="eri-modal-overlay" style="z-index:1001;"
             @click.self="errorModalOpen = false" data-error-ui>
            <div class="eri-card" style="width:100%;max-width:480px;" @click.stop data-error-ui>
                <h3 style="font-family:var(--display);font-size:18px;margin:0 0 12px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:var(--warn);"></i> Сообщить об ошибке
                </h3>
                <div class="eri-alert warn" style="margin-bottom:12px;font-style:italic;" x-text="'«' + errorSelectedText + '»'"></div>
                <label class="eri-label">Как должно быть? (необязательно)</label>
                <textarea x-model="errorSuggestion" rows="3" class="eri-textarea" style="margin-bottom:12px;"
                          placeholder="Напишите исправленный вариант..."></textarea>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <x-eriiba.btn variant="ghost" size="sm" x-on:click="errorModalOpen = false">Отмена</x-eriiba.btn>
                    <button class="eri-btn primary sm" @click="submitError()" :disabled="errorSubmitting">
                        <span x-show="!errorSubmitting">Отправить</span>
                        <span x-show="errorSubmitting" x-cloak><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
                <p x-show="errorSuccess" x-cloak style="text-align:center;color:var(--ok);font-weight:600;margin-top:10px;">Спасибо! Ошибка отправлена.</p>
                <p x-show="errorFail" x-cloak style="text-align:center;color:var(--err);font-weight:600;margin-top:10px;">Не удалось отправить. Попробуйте ещё раз.</p>
            </div>
        </div>
        </template>
    @endauth

    @auth
    <template x-teleport="body">
    <div x-show="reportOpen" x-cloak
         class="eri-modal-overlay" style="z-index:1001;"
         @click.self="reportOpen = false">
        <div class="eri-card" style="width:100%;max-width:480px;" @click.stop>
            <h3 style="font-family:var(--display);font-size:18px;margin:0 0 14px;">
                <i class="fa-solid fa-flag" style="color:var(--err);"></i> Пожаловаться на главу
            </h3>
            <label class="eri-label">Причина</label>
            <select x-model="reportReason" class="eri-select" style="margin-bottom:12px;">
                <option value="">— выберите причину —</option>
                <option value="Нарушение правил">Нарушение правил</option>
                <option value="Неуместный контент">Неуместный контент (18+, шок)</option>
                <option value="Оскорбления / разжигание">Оскорбления / разжигание ненависти</option>
                <option value="Плагиат / копия">Плагиат / копия</option>
                <option value="Спам / реклама">Спам / реклама</option>
                <option value="Низкое качество (нечитаемо)">Низкое качество (нечитаемо)</option>
                <option value="Другое">Другое</option>
            </select>
            <label class="eri-label">Подробности (необязательно)</label>
            <textarea x-model="reportDetails" rows="4" class="eri-textarea" style="margin-bottom:12px;"
                      placeholder="Что именно вас беспокоит?"></textarea>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <x-eriiba.btn variant="ghost" size="sm" x-on:click="reportOpen = false">Отмена</x-eriiba.btn>
                <button class="eri-btn primary sm" @click="submitReport()"
                        x-bind:disabled="reportSubmitting || !reportReason.trim()">
                    <span x-show="!reportSubmitting">Отправить</span>
                    <span x-show="reportSubmitting" x-cloak><i class="fa-solid fa-spinner fa-spin"></i></span>
                </button>
            </div>
            <p x-show="reportSuccess" x-cloak style="text-align:center;color:var(--ok);font-weight:600;margin-top:10px;">Спасибо! Жалоба отправлена модераторам.</p>
        </div>
    </div>
    </template>
    @endauth

    <template x-teleport="body">
    <div x-show="hookOpen" x-cloak
         class="eri-modal-overlay"
         @click.self="hookOpen = false" @keydown.escape.window="hookOpen = false">
        <div class="eri-card" style="width:100%;max-width:520px;max-height:80vh;overflow-y:auto;" @click.stop>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <i class="fa-solid fa-anchor" style="color:var(--warn);"></i>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;font-weight:700;color:var(--warn);text-transform:uppercase;letter-spacing:0.08em;">
                        Примечание <span x-text="'[' + hookIndex + ']'"></span>
                    </div>
                    <div style="font-family:var(--display);font-weight:500;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="'«' + hookText + '»'"></div>
                </div>
                <button @click="hookOpen = false" style="background:none;border:0;color:var(--text-3);font-size:20px;cursor:pointer;">×</button>
            </div>
            <div style="font-family:var(--serif);font-size:14px;line-height:1.6;color:var(--text-2);white-space:pre-wrap;" x-text="hookBody"></div>
        </div>
    </div>
    </template>

    <template x-teleport="body">
    <div x-show="lightboxOpen" x-cloak
         class="eri-modal-overlay" style="z-index:1100;background:rgba(0,0,0,0.92);"
         @click="lightboxOpen = false">
        <button @click="lightboxOpen = false"
                style="position:absolute;top:18px;right:18px;background:rgba(255,255,255,0.1);border:0;color:#fff;width:40px;height:40px;border-radius:50%;font-size:20px;cursor:pointer;">
            ×
        </button>
        <img :src="lightboxSrc" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:var(--r-md);" @click.stop>
    </div>
    </template>
</div>

<div id="reader-progress-bar"
     style="position:fixed;top:0;left:0;height:2px;background:var(--accent);width:0;z-index:80;transition:width 0.1s;"></div>

@push('scripts')
<style>
/* Дополнительные стили для контента главы (форматирование редактора)
   и крючков-сносок. .reader-body/.chapter-content задают шрифт/размер,
   мы только аккуратно вписываем редакторскую разметку. */
.reader-body .chapter-content { font-family: inherit; font-size: inherit; }
.reader-body p { text-indent: 1.5em; }
.reader-body p:first-of-type { text-indent: 0; }
.reader-body h2, .reader-body h3 { font-family: var(--display); margin-top: 1.4em; margin-bottom: 0.5em; color: var(--paper-text); text-indent: 0; }
.reader-body img { max-width: 100%; height: auto; display: block; margin: 1.4em auto; border-radius: var(--r-sm); cursor: zoom-in; }
.reader-body figcaption, .reader-body .attachment__caption,
.reader-body .attachment__name, .reader-body .attachment__size { display: none !important; }

.chapter-content .hook-mark {
    background: linear-gradient(transparent 65%, color-mix(in srgb, var(--warn) 35%, transparent) 65%);
    border-bottom: 1px dashed var(--warn);
    cursor: pointer; padding: 0 1px;
}
.chapter-content .hook-mark::after {
    content: '[' attr(data-hook-index) ']';
    font-size: 0.7em; vertical-align: super;
    color: var(--warn); font-weight: 700; margin-left: 1px;
}
.chapter-content .hook-mark:not([data-hook-index])::after { content: none; }
</style>
<script>
function readerData() {
    return {
        // sidebar / search
        tocQuery: '',
        // ui state
        chaptersOpen: false, commentsOpen: false,
        lightboxOpen: false, lightboxSrc: '',
        betaNoteOpen: false, betaNoteContent: '', betaNoteSubmitting: false, betaNoteSuccess: false,
        hookOpen: false, hookBody: '', hookIndex: 0, hookText: '',
        errorModalOpen: false, errorBubbleVisible: false,
        errorBubbleX: 0, errorBubbleY: 0,
        errorSelectedText: '', errorSuggestion: '',
        errorSubmitting: false, errorSuccess: false, errorFail: false,
        // settings (persist)
        fontSize: parseInt(localStorage.getItem('reader_font_size') || '19', 10),
        readerWidth: parseInt(localStorage.getItem('reader_width') || '720', 10),
        // currentTheme внутри читалки может быть 'sepia' (только для читалки) либо
        // зеркалит глобальную тему (light/dark). Sepia сохраняется отдельно как
        // reader_paper, чтобы при выходе из читалки сепия не «утекала» на сайт.
        currentTheme: (localStorage.getItem('reader_paper') === 'sepia')
            ? 'sepia'
            : (localStorage.getItem('theme') || 'light'),
        // По умолчанию сайдбар скрыт (immersive=true). Пользователь может открыть его
        // кнопкой в тулбаре — состояние сохранится в localStorage и применится на след. главе.
        // Значение '0' = пользователь явно открыл сайдбар; всё остальное (null/'1') = скрыт.
        immersive: localStorage.getItem('reader_immersive') !== '0',
        isFullscreen: false,
        bookmarked: false,
        bookmarkBusy: false,
        reportOpen: false, reportReason: '', reportDetails: '',
        reportSubmitting: false, reportSuccess: false,
        // chapter
        currentChapterId: {{ $chapter->id }},
        novelId: {{ $novel->id }},
        commentsCount: {{ $chapter->comments->count() }},
        commentsLoading: false, commentsError: null,
        commentsData: [], newCommentContent: '', postingComment: false,

        cycleTheme() {
            const order = ['light', 'sepia', 'dark'];
            const cur = order.indexOf(this.currentTheme);
            const next = order[(cur + 1) % order.length];
            this.currentTheme = next;
            this.applyReaderTheme();
        },
        applyReaderTheme() {
            const root = document.documentElement;
            const reader = document.getElementById('reader-scroll-area');
            if (this.currentTheme === 'sepia') {
                // Sepia применяем ТОЛЬКО к читалке через data-paper, html.theme не трогаем
                localStorage.setItem('reader_paper', 'sepia');
                if (reader) reader.setAttribute('data-paper', 'sepia');
            } else {
                // light/dark — обычная глобальная тема
                localStorage.removeItem('reader_paper');
                if (reader) reader.removeAttribute('data-paper');
                localStorage.setItem('theme', this.currentTheme);
                root.setAttribute('data-theme', this.currentTheme);
                root.classList.toggle('dark', this.currentTheme === 'dark');
                fetch('{{ route("theme.toggle") }}', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ theme: this.currentTheme })
                }).catch(() => {});
            }
        },
        cycleWidth() {
            const widths = [640, 720, 880, 1100];
            const cur = widths.indexOf(this.readerWidth);
            this.readerWidth = widths[(cur + 1) % widths.length] || 720;
        },
        toggleImmersive() {
            this.immersive = !this.immersive;
            localStorage.setItem('reader_immersive', this.immersive ? '1' : '0');
        },
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                (document.documentElement.requestFullscreen?.() || Promise.reject()).catch(() => {});
                this.isFullscreen = true;
            } else {
                document.exitFullscreen?.();
                this.isFullscreen = false;
            }
        },
        async loadBookmark() {
            try {
                const r = await fetch(`/api/chapter/${this.currentChapterId}/bookmark`);
                if (r.ok) { const d = await r.json(); this.bookmarked = !!d.bookmarked; }
            } catch(e) {}
        },
        async toggleBookmark() {
            if (this.bookmarkBusy) return;
            this.bookmarkBusy = true;
            try {
                const total = document.documentElement.scrollHeight - window.innerHeight;
                const pct = total > 0 ? (window.scrollY / total) * 100 : 0;
                const r = await fetch(`/api/chapter/${this.currentChapterId}/bookmark`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ percent: pct })
                });
                if (r.ok) { const d = await r.json(); this.bookmarked = !!d.bookmarked; }
                else if (r.status === 401) window.location.href = '{{ route("login") }}';
            } catch(e) {}
            this.bookmarkBusy = false;
        },
        async submitReport() {
            if (!this.reportReason.trim() || this.reportSubmitting) return;
            this.reportSubmitting = true;
            try {
                const r = await fetch(`/api/chapter/${this.currentChapterId}/complaint`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ reason: this.reportReason.trim(), details: this.reportDetails.trim() })
                });
                if (r.ok) {
                    this.reportSuccess = true; this.reportReason = ''; this.reportDetails = '';
                    setTimeout(() => { this.reportOpen = false; this.reportSuccess = false; }, 1800);
                } else if (r.status === 401) window.location.href = '{{ route("login") }}';
            } catch(e) {}
            this.reportSubmitting = false;
        },

        init() {
            this.$watch('fontSize',    v => localStorage.setItem('reader_font_size', String(v)));
            this.$watch('readerWidth', v => localStorage.setItem('reader_width', String(v)));
            // Применяем reader_paper=sepia при загрузке (если выбрано)
            this.applyReaderTheme();
            @auth this.loadBookmark(); @endauth

            // Восстановление позиции в главе — без «дёрганий».
            // Стратегия: скрываем body opacity:0, делаем серию скроллов с увеличивающимися
            // задержками (картинки/шрифты грузятся → высота меняется), как только высота
            // стабилизируется — финальный scroll и плавный fade-in. Юзер не видит прыжков.
            const initialPercent = {{ $initialPercent ?? 'null' }};
            const savedPercent   = localStorage.getItem('read_percent_{{ $chapter->id }}');
            const pct = initialPercent ?? (savedPercent ? parseFloat(savedPercent) : null);

            if (pct != null && pct > 0.5) {
                // Скрываем body до восстановления (предотвращает визуальный jerk)
                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.18s ease';

                let lastHeight = 0;
                let stableCount = 0;
                const targetScroll = () => {
                    const total = document.documentElement.scrollHeight - window.innerHeight;
                    return total > 0 ? Math.round((pct / 100) * total) : 0;
                };
                // Жёсткий instant-skip без smooth (smooth тоже выглядит как «дёрганье»)
                const hardScroll = () => {
                    const oldBehavior = document.documentElement.style.scrollBehavior;
                    document.documentElement.style.scrollBehavior = 'auto';
                    window.scrollTo(0, targetScroll());
                    document.documentElement.style.scrollBehavior = oldBehavior;
                };
                hardScroll();

                // Поллинг: ждём, пока scrollHeight стабилизируется (картинки догрузились)
                const settle = () => {
                    const h = document.documentElement.scrollHeight;
                    if (h === lastHeight) {
                        stableCount += 1;
                    } else {
                        lastHeight = h;
                        stableCount = 0;
                        hardScroll();
                    }
                    if (stableCount >= 3) {
                        // Высота не менялась 3 тика подряд → готово
                        hardScroll();
                        document.body.style.opacity = '';   // fade-in
                        return;
                    }
                    setTimeout(settle, 90);
                };
                setTimeout(settle, 60);

                // Безопасный таймаут: даже если что-то пошло не так, через 2.5 сек показываем
                setTimeout(() => { document.body.style.opacity = ''; }, 2500);
            }

            // Прогресс по window scroll
            const updateProgress = () => {
                const total = document.documentElement.scrollHeight - window.innerHeight;
                const p = total > 0 ? (window.scrollY / total) * 100 : 0;
                const bar = document.getElementById('reader-progress-bar');
                if (bar) bar.style.width = p + '%';
                localStorage.setItem('read_percent_' + this.currentChapterId, String(p));
            };
            window.addEventListener('scroll', updateProgress, { passive: true });
            updateProgress();

            // Авто-сохранение прогресса каждые 4 сек
            @auth
            setInterval(() => {
                const total = document.documentElement.scrollHeight - window.innerHeight;
                const p = total > 0 ? (window.scrollY / total) * 100 : 0;
                fetch('{{ route("progress.save") }}', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ novel_id: this.novelId, chapter_id: this.currentChapterId, percent: p })
                }).catch(() => {});
            }, 4000);
            @endauth

            // Lightbox для картинок главы
            setTimeout(() => {
                const cont = document.getElementById('chapters-container');
                if (cont) {
                    cont.querySelectorAll('img').forEach((img, i) => {
                        if (!img.dataset.lightbox) {
                            img.dataset.lightbox = '1';
                            img.loading = i < 2 ? 'eager' : 'lazy';
                            img.addEventListener('click', (e) => {
                                e.stopPropagation();
                                this.lightboxSrc = img.src;
                                this.lightboxOpen = true;
                            });
                        }
                    });
                }
            }, 200);

            // Крючки (сноски)
            setTimeout(() => {
                const cont = document.getElementById('chapters-container');
                if (!cont) return;
                const hooks = cont.querySelectorAll('.hook-mark[data-hook-body]:not([data-hook-wired])');
                let idx = cont.querySelectorAll('.hook-mark[data-hook-index]').length;
                hooks.forEach((el) => {
                    idx += 1;
                    el.setAttribute('data-hook-index', String(idx));
                    el.setAttribute('data-hook-wired', '1');
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.hookIndex = parseInt(el.getAttribute('data-hook-index') || '0', 10);
                        this.hookBody  = el.getAttribute('data-hook-body') || '';
                        this.hookText  = (el.textContent || '').trim();
                        this.hookOpen  = true;
                    });
                });
            }, 200);

            @auth
            // «Сообщить об ошибке» по выделению
            document.addEventListener('mouseup', (e) => {
                if (e.target.closest('[data-error-ui]')) return;
                setTimeout(() => {
                    const sel = window.getSelection();
                    const text = sel ? sel.toString().trim() : '';
                    if (!text || text.length < 3) { this.errorBubbleVisible = false; return; }
                    const cont = document.getElementById('chapters-container');
                    if (!cont || !sel.rangeCount) { this.errorBubbleVisible = false; return; }
                    const range = sel.getRangeAt(0);
                    if (!cont.contains(range.commonAncestorContainer)) { this.errorBubbleVisible = false; return; }
                    const rect = range.getBoundingClientRect();
                    this.errorBubbleX = rect.left + rect.width/2;
                    this.errorBubbleY = rect.top; // position:fixed → viewport coords
                    this.errorSelectedText = text;
                    this.errorBubbleVisible = true;
                }, 10);
            });
            document.addEventListener('selectionchange', () => {
                const sel = window.getSelection();
                if (!sel || !sel.toString().trim()) {
                    setTimeout(() => { if (!this.errorModalOpen) this.errorBubbleVisible = false; }, 200);
                }
            });
            @endauth
        },

        openErrorModal() {
            this.errorBubbleVisible = false;
            this.errorSuggestion = ''; this.errorSuccess = false; this.errorFail = false;
            this.errorModalOpen = true;
            window.getSelection()?.removeAllRanges();
        },
        async submitError() {
            if (!this.errorSelectedText.trim() || this.errorSubmitting) return;
            this.errorSubmitting = true; this.errorFail = false;
            try {
                const r = await fetch(`/api/chapter/${this.currentChapterId}/error`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ selected_text: this.errorSelectedText, suggestion: this.errorSuggestion })
                });
                if (r.ok) { this.errorSuccess = true; setTimeout(() => { this.errorModalOpen = false; this.errorSuccess = false; }, 1800); }
                else if (r.status === 401) window.location.href = '{{ route("login") }}';
                else this.errorFail = true;
            } catch(e) { this.errorFail = true; }
            this.errorSubmitting = false;
        },

        async submitBetaNote() {
            if (!this.betaNoteContent.trim() || this.betaNoteSubmitting) return;
            this.betaNoteSubmitting = true;
            try {
                const r = await fetch(`/api/chapter/${this.currentChapterId}/beta-note`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ content: this.betaNoteContent.trim() })
                });
                if (r.ok) {
                    this.betaNoteSuccess = true; this.betaNoteContent = '';
                    setTimeout(() => { this.betaNoteOpen = false; this.betaNoteSuccess = false; }, 1800);
                } else if (r.status === 401) window.location.href = '{{ route("login") }}';
            } catch(e) {}
            this.betaNoteSubmitting = false;
        },

        async loadComments() {
            this.commentsLoading = true; this.commentsError = null;
            try {
                const r = await fetch(`/api/chapter/${this.currentChapterId}/comments`);
                if (!r.ok) throw new Error('Ошибка загрузки');
                const data = await r.json();
                this.commentsData  = data.comments;
                this.commentsCount = data.count;
            } catch(e) { this.commentsError = e.message || 'Не удалось загрузить'; }
            this.commentsLoading = false;
            // Ждём, пока Alpine отрендерит <template x-if>, иначе #comments-list ещё не в DOM
            this.$nextTick(() => this.renderComments());
        },
        renderComments() {
            const list = document.getElementById('comments-list');
            if (!list) return;
            list.onclick = (e) => {
                const btn = e.target.closest('[data-comment-like]');
                if (btn) this.toggleCommentLike(parseInt(btn.dataset.commentLike, 10));
            };
            if (!this.commentsData.length) {
                list.innerHTML = '<p style="text-align:center;padding:30px;color:var(--text-muted);font-style:italic;">Здесь пока тихо... Будьте первым!</p>';
                return;
            }
            const renderC = (c) => `
                <div style="display:flex;gap:12px;">
                    <div class="eri-avatar" style="width:36px;height:36px;flex:none;font-size:13px;">
                        ${c.user.avatar_url ? `<img src="${c.user.avatar_url}" alt="">` : (c.user.name||'U').charAt(0).toUpperCase()}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;color:var(--text);font-size:13px;">${escapeHtml(c.user.name)}</div>
                        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 12px;margin-top:4px;font-size:14px;color:var(--text-2);">
                            ${(c.content_html && /<[a-z]/i.test(c.content_html)) ? c.content_html : escapeHtml(c.content)}
                        </div>
                        <button data-comment-like="${c.id}" style="background:none;border:0;color:${c.is_liked?'var(--err)':'var(--text-muted)'};font-size:12px;font-weight:600;cursor:pointer;margin-top:6px;">
                            <i class="${c.is_liked?'fa-solid':'fa-regular'} fa-heart"></i> ${c.likes_count}
                        </button>
                    </div>
                </div>
            `;
            list.innerHTML = this.commentsData.map(renderC).join('');
        },
        async postComment() {
            if (!this.newCommentContent.trim() || this.postingComment) return;
            this.postingComment = true;
            try {
                const r = await fetch(`/api/chapter/${this.currentChapterId}/comments`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify({ content: this.newCommentContent.trim() })
                });
                const data = await r.json();
                if (r.ok) {
                    this.newCommentContent = '';
                    this.commentsData.push(data.comment);
                    this.commentsCount = (this.commentsCount || 0) + 1;
                    this.$nextTick(() => this.renderComments());
                } else if (r.status === 401) window.location.href = '{{ route("login") }}';
                else if (data.error) alert(data.error);
            } catch(e) {}
            this.postingComment = false;
        },
        async toggleCommentLike(id) {
            try {
                const r = await fetch(`/api/comments/${id}/like`, {
                    method: 'POST',
                    headers: { 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' }
                });
                const data = await r.json();
                if (r.ok) {
                    const c = this.commentsData.find(x => x.id === id);
                    if (c) { c.likes_count = data.likes_count; c.is_liked = data.is_liked; }
                    this.$nextTick(() => this.renderComments());
                } else if (r.status === 401) window.location.href = '{{ route("login") }}';
            } catch(e) {}
        }
    };
}
function escapeHtml(s) { const d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }
</script>
@endpush
@endsection
