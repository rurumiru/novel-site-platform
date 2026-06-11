@extends('layouts.app')
@section('title', $novel->title)

@php
    $coverUrl = $novel->cover_image ? \App\Models\Novel::storageUrl($novel->cover_image) : null;
    $bgUrl    = $novel->background_image ? \App\Models\Novel::storageUrl($novel->background_image) : $coverUrl;

    $statusMap = [
        'ongoing'   => ['label' => 'Выходит',   'color' => 'var(--ok)'],
        'completed' => ['label' => 'Завершён',  'color' => 'var(--accent)'],
        'hiatus'    => ['label' => 'Заморожен', 'color' => 'var(--warn)'],
    ];
    $st = $statusMap[$novel->status] ?? ['label' => $novel->status, 'color' => 'var(--text-3)'];

    $publishedChapters    = $novel->publishedChapters;
    $publishedCount       = $publishedChapters->count();
    $totalWords           = (int) $publishedChapters->sum(function($c) { return mb_strlen(strip_tags($c->content ?? '')) / 5.5; });
    $favoritesCount       = $novel->favorites()->count();
    $totalLikes           = $novel->total_likes ?? 0;
    $avgRating            = (float) $novel->average_rating;
    $ratingsCount         = $novel->ratings()->count();
    $lastChapter          = $publishedChapters->last();
    $lastPublished        = $lastChapter ? \Carbon\Carbon::parse($lastChapter->published_at ?? $lastChapter->created_at) : null;

    $authorInitials = mb_strtoupper(mb_substr($novel->publisher?->name ?? 'A', 0, 2));
    $firstChapter   = $publishedChapters->first();
    $lastReadCh     = $novel->last_read_chapter;
    $continueChapter = $lastReadCh ?: $firstChapter;
@endphp

@section('content')

<section class="novel-banner {{ $novel->background_image ? 'has-bg' : 'cover-fallback' }}">
    <div class="novel-banner-bg">
        @if($bgUrl)
            <img src="{{ $bgUrl }}" alt="" fetchpriority="high" loading="eager" decoding="async">
        @endif
    </div>
    <div class="novel-banner-overlay"></div>
</section>

<section class="novel-head eri-section">
    <div class="novel-head-cover">
        <x-eriiba.cover :novel="$novel" />

        <div class="novel-actions-row" style="margin-top:16px;flex-direction:column;gap:8px;">
            @if($novel->next_chapter_at && $novel->next_chapter_at->isFuture())
                <x-chapter-countdown :date="$novel->next_chapter_at" />
            @endif

            @if($continueChapter)
                <x-eriiba.btn variant="primary" block :href="route('novel.read', [$novel->id, $continueChapter->id])"
                              icon="{{ $lastReadCh ? 'play' : 'book-open' }}">
                    {{ $lastReadCh ? 'Продолжить' : 'Начать читать' }}
                </x-eriiba.btn>
            @endif

            @livewire('favorite-button', ['novel' => $novel])

            @auth
                @if($novel->publisher && $novel->publisher->id !== Auth::id())
                    <x-eriiba.btn block :href="route('messages', ['user' => $novel->publisher->id])" icon="message">
                        Написать издателю
                    </x-eriiba.btn>
                @endif
            @endauth
        </div>

        <div class="eri-row" style="justify-content:center;gap:24px;margin-top:14px;font-size:13px;color:var(--text-3);">
            <span class="eri-stat" title="В избранном"><i class="fa-solid fa-heart" style="color:var(--err);"></i> {{ number_format($favoritesCount) }}</span>
            <span class="eri-stat" title="Лайков на главах"><i class="fa-solid fa-thumbs-up" style="color:var(--accent);"></i> {{ number_format($totalLikes) }}</span>
        </div>

        <div style="margin-top:18px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px;text-align:center;">Поделиться</div>
            <x-share-buttons :url="route('novel.show', $novel->id)" :title="$novel->title" class="eri-row" style="justify-content:center;" />
        </div>

        <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);display:grid;gap:8px;font-size:12px;">
            @if($novel->release_year)
                <div class="eri-row" style="justify-content:space-between;">
                    <span class="eri-text-3">Год</span>
                    <span style="font-weight:600;color:var(--text-2);">{{ $novel->release_year }}</span>
                </div>
            @endif
            <div class="eri-row" style="justify-content:space-between;">
                <span class="eri-text-3">Опубликовал</span>
                <a href="{{ route('users.show', $novel->publisher?->id ?? 1) }}" wire:navigate
                   style="color:var(--accent);font-weight:600;text-decoration:none;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $novel->publisher?->name ?? 'Система' }}
                </a>
            </div>
            @if($novel->original_author)
                <div class="eri-row" style="justify-content:space-between;">
                    <span class="eri-text-3">Оригинал</span>
                    <span style="font-weight:600;color:var(--text-2);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $novel->original_author }}</span>
                </div>
            @endif
            @if($novel->original_source)
                <div class="eri-row" style="justify-content:space-between;">
                    <span class="eri-text-3">Источник</span>
                    <a href="{{ $novel->original_source }}" target="_blank" rel="noopener"
                       style="color:var(--accent);font-weight:600;text-decoration:none;">
                        Ссылка <i class="fa-solid fa-external-link-alt" style="font-size:9px;"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="novel-head-meta">
        @php
            $genreClassMap = [
                'фэнтези' => 'fantasy', 'fantasy' => 'fantasy',
                'роман' => 'romance', 'романтика' => 'romance', 'romance' => 'romance',
                'экшен' => 'action', 'боевик' => 'action', 'action' => 'action',
                'sci-fi' => 'scifi', 'фантастика' => 'scifi',
                'мистика' => 'mystery', 'mystery' => 'mystery',
                'повседневность' => 'slice', 'slice' => 'slice',
                'хоррор' => 'horror', 'ужасы' => 'horror', 'horror' => 'horror',
                'litrpg' => 'litrpg', 'литрпг' => 'litrpg',
                'триллер' => 'mystery', 'thriller' => 'mystery',
                'комедия' => 'slice', 'юмор' => 'slice',
                'драма' => 'romance',
            ];
            $resolveVariant = function($name) use ($genreClassMap) {
                $low = mb_strtolower($name);
                foreach ($genreClassMap as $needle => $cls) {
                    if (mb_strpos($low, $needle) !== false) return $cls;
                }
                return null;
            };
        @endphp
        <div class="novel-eyebrow">
            @foreach($novel->genres as $genre)
                <x-eriiba.chip :variant="$resolveVariant($genre->name)" :href="route('catalog', ['genre' => $genre->id])">{{ $genre->name }}</x-eriiba.chip>
            @endforeach
            @foreach($novel->tags as $tag)
                <x-eriiba.chip :href="route('catalog', ['tags' => $tag->id])">#{{ $tag->name }}</x-eriiba.chip>
            @endforeach
            @if($novel->is_adult)<x-eriiba.chip variant="danger">18+</x-eriiba.chip>@endif
            <span class="novel-status" style="color: {{ $st['color'] }};">● {{ $st['label'] }}</span>
        </div>

        <h1 class="display novel-title">{{ $novel->title }}</h1>

        <div class="novel-author">
            <span>от</span>
            <a href="{{ route('users.show', $novel->publisher?->id ?? 1) }}" class="novel-author-link" wire:navigate>{{ $novel->publisher?->name ?? 'Система' }}</a>
        </div>

        <div class="novel-rating-row">
            <div class="novel-rating-num display">{{ number_format($avgRating, 1) }}</div>
            <div class="novel-rating-stars">
                <x-eriiba.stars :value="$avgRating" :size="18" />
                <div class="novel-rating-count">{{ number_format($ratingsCount) }} {{ \Illuminate\Support\Str::plural('оценка', $ratingsCount) }}</div>
            </div>
        </div>

        <div class="novel-stats">
            <div><strong>{{ $publishedCount }}</strong><span>глав</span></div>
            <div><strong>{{ $totalWords > 1000 ? round($totalWords/1000) . 'K' : $totalWords }}</strong><span>слов</span></div>
            <div><strong>{{ number_format($favoritesCount) }}</strong><span>читают</span></div>
            <div><strong>{{ number_format($stats['total']) }}</strong><span>прочтений</span></div>
            @if($lastPublished)
                <div><strong>{{ $lastPublished->diffForHumans(null, true) }}</strong><span>назад обновл.</span></div>
            @endif
        </div>

        <div class="novel-syn-block" x-data="{ open: false }">
            <h2 class="display novel-h2" style="font-size:22px; margin-top:28px; margin-bottom:14px;">Синопсис</h2>
            <div class="novel-syn eriiba-serif" :class="{ 'open': open }">
                {!! \App\Services\ContentRenderer::toHtml($novel->description ?? '') !!}
            </div>
            <button class="eri-btn ghost sm" @click="open = !open" style="margin-top:8px;">
                <span x-text="open ? 'Свернуть ↑' : 'Читать дальше ↓'"></span>
            </button>

            @if($novel->extra_info)
                <div class="eri-alert warn" style="margin-top:16px;">{!! nl2br(e($novel->extra_info)) !!}</div>
            @endif
        </div>

    </div>
</section>

@php
    $novelReviews = \Illuminate\Support\Facades\Schema::hasTable('reviews')
        ? \App\Models\Review::where('is_published', true)
            ->where('novel_id', $novel->id)
            ->with('user:id,name,username,avatar')
            ->orderByDesc('recommends_count')
            ->orderByDesc('created_at')
            ->take(12)->get()
        : collect();
@endphp

<section class="eri-section novel-cols">
    <div class="novel-main">

        <div class="novel-sect" x-data="{ q: '' }">
            <div class="novel-sect-head">
                <h2 class="display novel-h2">Оглавление</h2>
                <div class="novel-sect-controls">
                    <input type="search" class="eri-input" x-model="q" placeholder="⌕ Поиск глав" style="max-width:240px;font-size:13px;padding:7px 12px;border-radius:var(--r-pill);">
                </div>
            </div>

            @php $chapterIndex = 0; @endphp
            <div class="ch-table">
                <div class="ch-table-head">
                    <span>№</span>
                    <span>Название</span>
                    <span style="text-align:right;">Параметры</span>
                </div>

                @foreach($volumes as $volIdx => $volume)
                    @php $volChapterIdx = 0; @endphp
                    <div x-data="{ open: {{ $volIdx === 0 ? 'true' : 'true' }} }" style="grid-column:1/-1;">
                        <button type="button" @click="open = !open"
                                style="grid-column:1/-1;width:100%;padding:12px 18px;background:var(--surface-2);
                                       border-bottom:1px solid var(--border);font-family:var(--display);
                                       font-size:13px;font-weight:600;color:var(--text-2);letter-spacing:-0.01em;
                                       cursor:pointer;display:flex;align-items:center;justify-content:space-between;
                                       border:0;border-bottom:1px solid var(--border);text-align:left;">
                            <span style="display:flex;align-items:center;gap:10px;">
                                <i class="fa-solid fa-chevron-down" x-bind:style="open ? '' : 'transform:rotate(-90deg);'" style="font-size:10px;color:var(--text-muted);transition:transform 0.15s;"></i>
                                {{ $volume->title }}
                                <span style="font-family:var(--mono);font-size:11px;color:var(--text-muted);font-weight:500;letter-spacing:0.04em;">{{ $volume->chapters->count() }} гл.</span>
                            </span>
                        </button>
                        <div x-show="open" x-collapse>
                    @foreach($volume->chapters as $chapter)
                        @php
                            $chapterIndex++;
                            $volChapterIdx++;
                            $hidden  = !empty($guestLimitEnabled) && $chapterIndex > $guestChapterLimit;
                            $words   = (int) round(mb_strlen(strip_tags($chapter->content ?? '')) / 5.5);
                            $when    = ($chapter->published_at ?? $chapter->created_at)?->diffForHumans(null, true, true);
                        @endphp
                        @if($hidden)
                            <div class="ch-row" style="opacity:0.5;cursor:not-allowed;" x-show="q === '' || '{{ \Illuminate\Support\Str::lower(addslashes($chapter->title)) }}'.includes(q.toLowerCase())">
                                <div class="ch-num">{{ $volChapterIdx }}</div>
                                <div class="ch-title"><i class="fa-solid fa-eye-slash" style="margin-right:6px;color:var(--text-muted);"></i>{{ $chapter->title }}</div>
                                <div class="ch-meta"><span class="ch-lock">только для зарегистрированных</span></div>
                            </div>
                        @else
                            @php
                                $hl = $chapter->highlight;
                                $hlLabels = [
                                    'important' => '🔥 Важная', 'arc-end' => '🏁 Конец арки',
                                    'arc-start' => '🚀 Начало арки', 'breakthrough' => '⚡ Прорыв',
                                    'special' => '✨ Спецглава', 'side-story' => '📖 Побочка',
                                    'announcement' => '📢 Объявление',
                                ];
                            @endphp
                            <a href="{{ route('novel.read', [$novel->id, $chapter->id]) }}" wire:navigate
                               class="ch-row {{ $hl ? 'hl hl-' . $hl : '' }}"
                               x-show="q === '' || '{{ \Illuminate\Support\Str::lower(addslashes($chapter->title)) }}'.includes(q.toLowerCase())">
                                <div class="ch-num">{{ $volChapterIdx }}</div>
                                <div class="ch-title">
                                    {{ $chapter->title }}
                                    @if($hl && isset($hlLabels[$hl]))
                                        <span class="ch-hl-badge hl-{{ $hl }}" style="margin-left:8px;">{{ $hlLabels[$hl] }}</span>
                                    @endif
                                </div>
                                <div class="ch-meta">
                                    @if($words > 0)<span>{{ number_format($words) }} сл</span><span>·</span>@endif
                                    @if($when)<span>{{ $when }}</span>@endif
                                    @if($chapter->is_locked)
                                        <span class="ch-lock"><i class="fa-solid fa-lock" style="font-size:9px;margin-right:3px;"></i>Premium</span>
                                        @if(($chapter->price ?? 0) > 0)
                                            <span class="ch-price"><i class="fa-solid fa-ruble-sign" style="font-size:9px;margin-right:3px;"></i>{{ $chapter->price }} ₽</span>
                                        @endif
                                    @endif
                                </div>
                            </a>
                        @endif
                    @endforeach
                        </div>
                    </div>
                @endforeach

                @if($chaptersWithoutVolume->count() > 0)
                    <div x-data="{ open: true }" style="grid-column:1/-1;">
                        @if($volumes->count() > 0)
                            <button type="button" @click="open = !open"
                                    style="grid-column:1/-1;width:100%;padding:12px 18px;background:var(--surface-2);
                                           border-bottom:1px solid var(--border);font-family:var(--display);
                                           font-size:13px;font-weight:600;color:var(--text-2);letter-spacing:-0.01em;
                                           cursor:pointer;display:flex;align-items:center;justify-content:space-between;
                                           border:0;border-bottom:1px solid var(--border);text-align:left;">
                                <span style="display:flex;align-items:center;gap:10px;">
                                    <i class="fa-solid fa-chevron-down" x-bind:style="open ? '' : 'transform:rotate(-90deg);'" style="font-size:10px;color:var(--text-muted);transition:transform 0.15s;"></i>
                                    Без тома
                                    <span style="font-family:var(--mono);font-size:11px;color:var(--text-muted);font-weight:500;letter-spacing:0.04em;">{{ $chaptersWithoutVolume->count() }} гл.</span>
                                </span>
                            </button>
                        @endif
                        <div x-show="open" x-collapse>
                    @php $noVolIdx = 0; @endphp
                    @foreach($chaptersWithoutVolume as $chapter)
                        @php
                            $chapterIndex++;
                            $noVolIdx++;
                            $hidden = !empty($guestLimitEnabled) && $chapterIndex > $guestChapterLimit;
                            $words  = (int) round(mb_strlen(strip_tags($chapter->content ?? '')) / 5.5);
                            $when   = ($chapter->published_at ?? $chapter->created_at)?->diffForHumans(null, true, true);
                        @endphp
                        @if($hidden)
                            <div class="ch-row" style="opacity:0.5;cursor:not-allowed;" x-show="q === '' || '{{ \Illuminate\Support\Str::lower(addslashes($chapter->title)) }}'.includes(q.toLowerCase())">
                                <div class="ch-num">{{ $noVolIdx }}</div>
                                <div class="ch-title"><i class="fa-solid fa-eye-slash" style="margin-right:6px;color:var(--text-muted);"></i>{{ $chapter->title }}</div>
                                <div class="ch-meta"><span class="ch-lock">только для зарегистрированных</span></div>
                            </div>
                        @else
                            @php
                                $hl = $chapter->highlight;
                                $hlLabels = [
                                    'important' => '🔥 Важная', 'arc-end' => '🏁 Конец арки',
                                    'arc-start' => '🚀 Начало арки', 'breakthrough' => '⚡ Прорыв',
                                    'special' => '✨ Спецглава', 'side-story' => '📖 Побочка',
                                    'announcement' => '📢 Объявление',
                                ];
                            @endphp
                            <a href="{{ route('novel.read', [$novel->id, $chapter->id]) }}" wire:navigate
                               class="ch-row {{ $hl ? 'hl hl-' . $hl : '' }}"
                               x-show="q === '' || '{{ \Illuminate\Support\Str::lower(addslashes($chapter->title)) }}'.includes(q.toLowerCase())">
                                <div class="ch-num">{{ $noVolIdx }}</div>
                                <div class="ch-title">
                                    {{ $chapter->title }}
                                    @if($hl && isset($hlLabels[$hl]))
                                        <span class="ch-hl-badge hl-{{ $hl }}" style="margin-left:8px;">{{ $hlLabels[$hl] }}</span>
                                    @endif
                                </div>
                                <div class="ch-meta">
                                    @if($words > 0)<span>{{ number_format($words) }} сл</span><span>·</span>@endif
                                    @if($when)<span>{{ $when }}</span>@endif
                                    @if($chapter->is_locked)
                                        <span class="ch-lock"><i class="fa-solid fa-lock" style="font-size:9px;margin-right:3px;"></i>Premium</span>
                                        @if(($chapter->price ?? 0) > 0)
                                            <span class="ch-price"><i class="fa-solid fa-ruble-sign" style="font-size:9px;margin-right:3px;"></i>{{ $chapter->price }} ₽</span>
                                        @endif
                                    @endif
                                </div>
                            </a>
                        @endif
                    @endforeach
                        </div>
                    </div>
                @endif

                @if($chapterIndex === 0)
                    <div style="padding:40px 20px;text-align:center;color:var(--text-muted);font-style:italic;">
                        Ещё нет опубликованных глав.
                    </div>
                @endif
            </div>

            @if(!empty($guestLimitEnabled) && $chapterIndex > $guestChapterLimit)
                <div class="eri-card" style="margin-top:14px;text-align:center;">
                    <p style="margin:0 0 12px;color:var(--text-2);font-size:13px;">
                        <i class="fa-solid fa-lock" style="margin-right:6px;color:var(--warn);"></i>
                        Зарегистрируйтесь, чтобы читать все главы новеллы.
                    </p>
                    <x-eriiba.btn variant="primary" :href="route('register')" size="sm">Регистрация</x-eriiba.btn>
                </div>
            @endif
        </div>

        @php
            $bulkSubscribed = $novel->is_subscribed ?? false;
            $bulkIsAuthor   = \Illuminate\Support\Facades\Auth::check() && $novel->user_id === \Illuminate\Support\Facades\Auth::id();
            $bulkHasPaid    = $novel->publishedChapters()->where('is_locked', true)->where('price', '>', 0)->exists();
        @endphp
        @if($bulkHasPaid && !$bulkSubscribed && !$bulkIsAuthor)
            <div class="novel-sect">
                @livewire('bulk-unlock-chapters', ['novel' => $novel])
            </div>
        @endif

        <div class="novel-sect">
            <div class="novel-sect-head">
                <h2 class="display novel-h2">Отзывы и оценки</h2>
            </div>

            <div class="rating-summary">
                <div class="rating-summary-num">
                    <div class="display rating-big">{{ number_format($avgRating, 1) }}</div>
                    <x-eriiba.stars :value="$avgRating" :size="18" />
                    <div class="rating-summary-count">из {{ number_format($ratingsCount) }} {{ \Illuminate\Support\Str::plural('оценка', $ratingsCount) }}</div>
                </div>
                <div class="rating-summary-bars">
                    
                    @auth
                        @livewire('rating-widget', ['novel' => $novel])
                    @else
                        <div class="eri-alert" style="margin:0;">
                            <a href="{{ route('login') }}" wire:navigate style="color:var(--accent);font-weight:600;">Войдите</a>,
                            чтобы поставить оценку этой новелле.
                        </div>
                    @endauth
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('reviews.index') && \Illuminate\Support\Facades\Route::has('reviews.show'))
            <div class="novel-reviews-section" x-data="{
                    scrollBy(dir){
                        const t = $refs.track; if(!t) return;
                        const step = t.clientWidth * 0.85;
                        t.scrollBy({ left: dir * step, behavior: 'smooth' });
                    }
                }">
                <div class="novel-reviews-head">
                    <h3 class="novel-reviews-title">Обзоры читателей</h3>
                    <a href="{{ route('reviews.index') }}?novel={{ $novel->id }}" wire:navigate class="novel-reviews-more">
                        Все обзоры <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
                    </a>
                </div>

                <div class="novel-reviews-slider">
                    <button type="button" class="novel-reviews-arrow left" @click="scrollBy(-1)" aria-label="Назад">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <div class="novel-reviews-track" x-ref="track">
                        @foreach($novelReviews as $r)
                            @php $bodyText = trim(strip_tags($r->body)); $bodyShort = \Illuminate\Support\Str::words($bodyText, 22, '…'); @endphp
                            <a href="{{ route('reviews.show', $r->id) }}" wire:navigate class="novel-review-card">
                                <div class="novel-review-head">
                                    @if($r->user?->avatar_url)
                                        <img src="{{ $r->user->avatar_url }}" alt="" class="novel-review-avatar">
                                    @else
                                        <span class="novel-review-avatar novel-review-avatar-text">{{ mb_strtoupper(mb_substr($r->user?->name ?? '?', 0, 1)) }}</span>
                                    @endif
                                    <div class="novel-review-meta-head">
                                        <div class="novel-review-author">{{ $r->user?->name ?? '—' }}</div>
                                        <div class="novel-review-cat">[{{ $r->category_label }}]</div>
                                    </div>
                                </div>
                                <div class="novel-review-title">«{{ $r->title }}»</div>
                                @if($bodyShort)<div class="novel-review-body">{{ $bodyShort }}</div>@endif
                                <div class="novel-review-stats">
                                    <span><i class="fa-regular fa-thumbs-up"></i> {{ $r->recommends_count }}</span>
                                    <span><i class="fa-regular fa-eye"></i> {{ $r->views_count }}</span>
                                    <span>·</span>
                                    <span>{{ $r->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach

                        @php $emptySlots = max(0, 3 - $novelReviews->count()); @endphp
                        @for($i = 0; $i < $emptySlots; $i++)
                            <div class="novel-review-empty">
                                <span>{{ $novelReviews->isEmpty() && $i === 0 ? 'Ещё нет обзоров. Будьте первым!' : 'Напишите свой обзор' }}</span>
                                @auth
                                    @if(\Illuminate\Support\Facades\Route::has('reviews.create'))
                                        <a href="{{ route('reviews.create') }}?novel_id={{ $novel->id }}" wire:navigate class="novel-review-empty-btn">
                                            <i class="fa-solid fa-pen"></i> Написать обзор
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" wire:navigate class="novel-review-empty-btn">
                                        <i class="fa-solid fa-pen"></i> Войти и написать
                                    </a>
                                @endauth
                            </div>
                        @endfor
                    </div>

                    <button type="button" class="novel-reviews-arrow right" @click="scrollBy(1)" aria-label="Далее">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            @endif

            <div x-data="{ tab: 'discussion' }">
                <div class="eri-tabs">
                    <button class="eri-tab" :class="tab === 'discussion' ? 'on' : ''" @click="tab = 'discussion'">
                        <i class="fa-regular fa-comments" style="margin-right:6px;"></i>Обсуждение
                    </button>
                    <button class="eri-tab" :class="tab === 'chapter_comments' ? 'on' : ''" @click="tab = 'chapter_comments'">
                        <i class="fa-solid fa-book-open" style="margin-right:6px;"></i>Из глав
                    </button>
                </div>

                <div x-show="tab === 'discussion'" class="eri-card">
                    @livewire('comments-section', ['model' => $novel])
                </div>

                <div x-show="tab === 'chapter_comments'" x-cloak class="eri-comments">
                    <div class="comment-list">
                        @forelse($novel->chapter_comments as $comment)
                            <div class="comment-item">
                                <div class="comment-card">
                                    <div class="comment-card-head">
                                        <a href="{{ route('users.show', $comment->user->id) }}" class="comment-avatar-wrap" wire:navigate>
                                            <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name=U' }}"
                                                 alt="{{ $comment->user->name }}" class="comment-avatar"
                                                 style="width:48px;height:48px;" loading="lazy">
                                        </a>
                                        <div class="comment-head-body">
                                            <div class="comment-name">
                                                <a href="{{ route('users.show', $comment->user->id) }}" wire:navigate class="comment-name-link">
                                                    {{ $comment->user->name }}
                                                </a>
                                            </div>
                                            <div class="comment-meta-row">
                                                <span class="comment-time">{{ $comment->created_at->format('d.m.y') }} написано</span>
                                                <a href="{{ route('novel.read', [$novel->id, $comment->commentable->id]) }}" wire:navigate
                                                   class="comment-chapter-ref">
                                                    <i class="fa-solid fa-book" style="font-size:10px;"></i>
                                                    Глава: {{ $comment->commentable->title }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment-body">
                                        {!! \App\Services\CommentContentRenderer::toSafeHtml($comment->content) ?: e($comment->content) !!}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="padding:40px;text-align:center;color:var(--text-muted);font-style:italic;">
                                Комментариев к главам пока нет.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <aside class="novel-side">
        <div class="side-card">
            <h3 class="side-h3">Об авторе</h3>
            <div class="author-card">
                <div class="av">
                    @if($novel->publisher?->avatar_url)
                        <img src="{{ $novel->publisher->avatar_url }}" alt="{{ $novel->publisher->name }}" loading="lazy">
                    @else
                        {{ $authorInitials }}
                    @endif
                </div>
                <div>
                    <div class="author-name display">{{ $novel->publisher?->name ?? 'Система' }}</div>
                    <div class="author-meta">
                        @if($novel->publisher)
                            {{ $novel->publisher->novels()->count() }} {{ \Illuminate\Support\Str::plural('новелла', $novel->publisher->novels()->count()) }}
                        @endif
                    </div>
                </div>
            </div>
            @if($novel->publisher?->bio)
                <p class="eriiba-serif side-text">{{ \Illuminate\Support\Str::limit($novel->publisher->bio, 200) }}</p>
            @endif
            <div class="author-actions">
                <x-eriiba.btn size="sm" :href="route('users.show', $novel->publisher?->id ?? 1)">Профиль</x-eriiba.btn>
                @auth
                    @if($novel->publisher && $novel->publisher->id !== Auth::id())
                        <x-eriiba.btn size="sm" variant="ghost" :href="route('messages', ['user' => $novel->publisher->id])" icon="message">
                            Написать
                        </x-eriiba.btn>
                    @endif
                @endauth
            </div>
        </div>

        @php
            $dlEnabled = filter_var(\App\Models\Setting::retrieve('downloads_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
            $dlFormats = json_decode(\App\Models\Setting::retrieve('download_formats', '["txt","fb2","epub"]'), true) ?? ['txt','fb2','epub'];
        @endphp
        @if($dlEnabled && count($dlFormats) > 0 && $publishedCount > 0)
            @php
                $hasVolumes = $volumes->count() > 0;
                $hasNoVol   = $chaptersWithoutVolume->count() > 0;
            @endphp
            <div class="side-card"
                 x-data="{
                    open: false,
                    fmt: @js($dlFormats[0] ?? 'epub'),
                    scope: 'all',
                    selectedVolumes: [],
                    novolume: {{ $hasNoVol ? 'true' : 'false' }},
                    buildUrl() {
                        let base = '{{ url('/novel/' . $novel->id . '/download') }}/' + this.fmt;
                        if (this.scope === 'all') return base;
                        const params = new URLSearchParams();
                        this.selectedVolumes.forEach(v => params.append('volumes[]', v));
                        if (this.novolume) params.append('novolume', '1');
                        const qs = params.toString();
                        return qs ? base + '?' + qs : base;
                    }
                 }">
                <h3 class="side-h3"><i class="fa-solid fa-download" style="margin-right:6px;"></i>Скачать</h3>

                <div style="margin-bottom:12px;">
                    <label class="eri-label" style="margin-bottom:6px;">Формат</label>
                    <div class="rk-pill-group" style="width:100%;">
                        @foreach($dlFormats as $fmt)
                            <button type="button" @click="fmt = '{{ $fmt }}'"
                                    x-bind:class="fmt === '{{ $fmt }}' ? 'on' : ''"
                                    style="flex:1; text-transform:uppercase; letter-spacing:0.05em;">{{ $fmt }}</button>
                        @endforeach
                    </div>
                </div>

                @if($hasVolumes)
                    
                    <div style="margin-bottom:10px;">
                        <label class="eri-label" style="margin-bottom:6px;">Охват</label>
                        <div class="rk-pill-group" style="width:100%;">
                            <button type="button" @click="scope='all'" x-bind:class="scope==='all'?'on':''" style="flex:1;">Вся новелла</button>
                            <button type="button" @click="scope='custom'" x-bind:class="scope==='custom'?'on':''" style="flex:1;">Выбрать тома</button>
                        </div>
                    </div>

                    <div x-show="scope === 'custom'" x-cloak style="display:grid; gap:6px; margin-bottom:12px; max-height:200px; overflow-y:auto; padding:8px; border:1px solid var(--border); border-radius:var(--r-sm); background:var(--surface-2);">
                        @foreach($volumes as $vol)
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-2); cursor:pointer;">
                                <input type="checkbox" x-model="selectedVolumes" value="{{ $vol->id }}" style="width:14px; height:14px; cursor:pointer;">
                                <span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $vol->title }}</span>
                                <span class="ch-num" style="font-size:11px;">{{ $vol->chapters->count() }}</span>
                            </label>
                        @endforeach
                        @if($hasNoVol)
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-2); cursor:pointer; padding-top:6px; border-top:1px solid var(--border);">
                                <input type="checkbox" x-model="novolume" style="width:14px; height:14px; cursor:pointer;">
                                <span style="flex:1;">Главы без тома</span>
                                <span class="ch-num" style="font-size:11px;">{{ $chaptersWithoutVolume->count() }}</span>
                            </label>
                        @endif
                    </div>
                @endif

                <a x-bind:href="buildUrl()"
                   class="eri-btn primary block"
                   x-bind:style="(scope === 'custom' && selectedVolumes.length === 0 && !novolume) ? 'opacity:.4; pointer-events:none;' : ''">
                    <i class="fa-solid fa-download"></i> Скачать
                </a>
            </div>
        @endif

        <div class="side-card">
            <h3 class="side-h3">Статистика</h3>
            <div class="progress-stat">
                <span>Всего глав</span>
                <strong>{{ $publishedCount }}</strong>
            </div>
            <div class="progress-stat">
                <span>Просмотры</span>
                <strong>{{ number_format($stats['total']) }}</strong>
            </div>
            <div class="progress-stat">
                <span>За месяц</span>
                <strong>{{ number_format($stats['month']) }}</strong>
            </div>
            @if($lastPublished)
                <div class="progress-stat">
                    <span>Последняя глава</span>
                    <strong>{{ $lastPublished->diffForHumans() }}</strong>
                </div>
            @endif
            @if($novel->next_scheduled_chapter)
                <div class="progress-stat">
                    <span>Следующая</span>
                    <strong>{{ $novel->next_scheduled_chapter->published_at->format('d.m H:i') }}</strong>
                </div>
            @endif
        </div>

        @php
            $genreIds = $novel->genres->pluck('id')->toArray();
            $tagIds = $novel->tags->pluck('id')->toArray();
            $isGuestSim = !\Illuminate\Support\Facades\Auth::check();
            $hideAdultSim = $isGuestSim && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);

            $similar = collect();
            if (!empty($genreIds)) {
                $similar = \App\Models\Novel::where('id', '!=', $novel->id)
                    ->where('is_published', true)
                    ->when($isGuestSim,    fn($q) => $q->where('hide_from_guests', false))
                    ->when($hideAdultSim,  fn($q) => $q->where('is_adult', false))
                    ->whereHas('genres', fn($q) => $q->whereIn('genres.id', $genreIds))
                    ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
                    ->orderByDesc('avg_rating')
                    ->orderByDesc('views')
                    ->take(6)->get();
            }
            if ($similar->count() < 4 && !empty($tagIds)) {
                $more = \App\Models\Novel::where('id', '!=', $novel->id)
                    ->whereNotIn('id', $similar->pluck('id'))
                    ->where('is_published', true)
                    ->when($isGuestSim,    fn($q) => $q->where('hide_from_guests', false))
                    ->when($hideAdultSim,  fn($q) => $q->where('is_adult', false))
                    ->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
                    ->orderByDesc('views')
                    ->take(6 - $similar->count())->get();
                $similar = $similar->concat($more);
            }
            if ($similar->count() < 4) {
                $more = \App\Models\Novel::where('id', '!=', $novel->id)
                    ->whereNotIn('id', $similar->pluck('id'))
                    ->where('is_published', true)
                    ->when($isGuestSim,    fn($q) => $q->where('hide_from_guests', false))
                    ->when($hideAdultSim,  fn($q) => $q->where('is_adult', false))
                    ->orderByDesc('views')
                    ->take(6 - $similar->count())->get();
                $similar = $similar->concat($more);
            }
        @endphp
        @if($similar->isNotEmpty())
            <div class="side-card">
                <h3 class="side-h3"><i class="fa-solid fa-sparkles" style="margin-right:6px;color:var(--accent);"></i>Похожие истории</h3>
                <div class="side-list">
                    @foreach($similar as $sim)
                        <a href="{{ route('novel.show', $sim->id) }}" wire:navigate class="side-item">
                            <x-eriiba.cover :novel="$sim" />
                            <div style="min-width:0;">
                                <div class="side-item-title">{{ $sim->title }}</div>
                                <div class="side-item-author">
                                    {{ $sim->publisher?->name ?? 'Автор' }}
                                    @if(($sim->avg_rating ?? 0) > 0)
                                        · <span style="color:var(--gold);">★ {{ number_format((float) $sim->avg_rating, 1) }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div style="margin-top:14px;text-align:center;">
                    <a href="{{ route('catalog', !empty($genreIds) ? ['genre' => $genreIds[0]] : []) }}" wire:navigate
                       style="font-size:12px;color:var(--accent);font-weight:600;text-decoration:none;">
                        Больше похожих →
                    </a>
                </div>
            </div>
        @endif
    </aside>
</section>

@endsection
