@extends('layouts.app')
@section('title', $review->title . ' — Обзоры работ')

@section('content')
@php
    $author       = $review->user;
    $canEdit      = false;
    $canModerate  = false;
    if (auth()->check()) {
        $u           = auth()->user();
        $canModerate = $u->hasRole(['owner','super_admin','deputy_admin','moderator']);
        $canEdit     = ($u->id === $review->user_id) || $canModerate;
    }

    $prevReview = \App\Models\Review::where('is_published', true)
        ->where('is_pinned', false)
        ->where('category', $review->category)
        ->where('id', '<', $review->id)
        ->orderByDesc('id')->first();
    $nextReview = \App\Models\Review::where('is_published', true)
        ->where('is_pinned', false)
        ->where('category', $review->category)
        ->where('id', '>', $review->id)
        ->orderBy('id')->first();

    $authorOtherPosts = \App\Models\Review::where('is_published', true)
        ->where('user_id', $review->user_id)
        ->where('id', '!=', $review->id)
        ->latest()->take(4)->get();
@endphp

<div class="reviews-show-page">

    <div class="rv-breadcrumbs">
        <a href="{{ route('reviews.index') }}" wire:navigate>
            <i class="fa-solid fa-arrow-left"></i> Обзоры работ
        </a>
        <span class="rv-sep">·</span>
        <a href="{{ route('reviews.index', ['tab' => $review->category]) }}" wire:navigate>
            [{{ $review->category_label }}]
        </a>
    </div>

    @if(session('success'))
        <div class="eri-alert ok" style="margin: 0 auto 18px; max-width:880px;">{{ session('success') }}</div>
    @endif

    <article class="rv-article">

        <header class="rv-head">
            <div class="rv-cat-row">
                <span class="rv-cat-badge rv-cat-{{ $review->category }}">
                    @if($review->category === 'notice')
                        <i class="fa-solid fa-bullhorn"></i> УВЕДОМЛЕНИЕ
                    @elseif($review->category === 'promotion')
                        <i class="fa-solid fa-bolt"></i> ПРОДВИЖЕНИЕ
                    @else
                        <i class="fa-solid fa-star"></i> ОБЗОР РАБОТ
                    @endif
                </span>
                @if($review->is_pinned)
                    <span class="rv-pinned"><i class="fa-solid fa-thumbtack"></i> Закреплено</span>
                @endif
            </div>

            <h1 class="rv-title">{{ $review->title }}</h1>

            <div class="rv-author-bar">
                <a href="{{ route('users.show', $review->user_id) }}" wire:navigate class="rv-author">
                    @if($author?->avatar_url)
                        <img src="{{ $author->avatar_url }}" alt="" class="rv-author-avatar">
                    @else
                        <span class="rv-author-avatar rv-author-avatar-text">{{ mb_strtoupper(mb_substr($author?->name ?? '?', 0, 1)) }}</span>
                    @endif
                    <div class="rv-author-info">
                        <div class="rv-author-name">
                            {{ $author?->name ?? '—' }}
                            @if($author?->is_verified)
                                <i class="fa-solid fa-circle-check" style="color:var(--accent);font-size:12px;" title="Верифицирован"></i>
                            @endif
                            @if($author?->is_premium)
                                <i class="fa-solid fa-crown" style="color:#f59e0b;font-size:11px;" title="Plus"></i>
                            @endif
                        </div>
                        @if($author?->username)
                            <div class="rv-author-username">{{ '@' . $author->username }}</div>
                        @endif
                    </div>
                </a>

                <div class="rv-meta-right">
                    <span><i class="fa-regular fa-calendar"></i> {{ $review->created_at->format('d.m.Y H:i') }}</span>
                    <span class="rv-meta-dot">·</span>
                    <span><i class="fa-regular fa-eye"></i> {{ $review->views_count }}</span>
                    <span class="rv-meta-dot">·</span>
                    <span><i class="fa-regular fa-thumbs-up"></i> {{ $review->recommends_count }}</span>
                    <span class="rv-meta-dot">·</span>
                    <span><i class="fa-regular fa-comment"></i> {{ $review->comments_count }}</span>
                </div>
            </div>

            @if($review->novel)
                <a href="{{ route('novel.show', $review->novel->id) }}" wire:navigate class="rv-novel-pin">
                    <div class="rv-novel-pin-cover">
                        <x-eriiba.cover :novel="$review->novel" />
                    </div>
                    <div class="rv-novel-pin-info">
                        <div class="rv-novel-pin-eyebrow"><i class="fa-solid fa-book"></i> Обзор на новеллу</div>
                        <div class="rv-novel-pin-title">{{ $review->novel->title }}</div>
                        <div class="rv-novel-pin-cta">Открыть страницу новеллы →</div>
                    </div>
                </a>
            @endif
        </header>

        <div class="rv-body prose">
            {!! $review->body !!}
        </div>

        <div class="rv-tools">
            <div class="rv-tools-left">
                @auth
                    <form method="POST" action="{{ route('reviews.recommend', $review) }}" class="rv-inline-form">
                        @csrf
                        <button type="submit" class="rv-tool-btn rv-recommend {{ $review->is_recommended ? 'on' : '' }}">
                            <i class="fa-solid fa-thumbs-up"></i>
                            <span>{{ $review->is_recommended ? 'Вы рекомендуете' : 'Рекомендовать' }}</span>
                            <span class="rv-tool-count">{{ $review->recommends_count }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="rv-tool-btn">
                        <i class="fa-solid fa-thumbs-up"></i>
                        <span>Рекомендовать</span>
                        <span class="rv-tool-count">{{ $review->recommends_count }}</span>
                    </a>
                @endauth

                <button type="button" class="rv-tool-btn" onclick="rvShareReview('{{ urlencode($review->title) }}', '{{ urlencode(url()->current()) }}')">
                    <i class="fa-solid fa-share-nodes"></i>
                    <span>Поделиться</span>
                </button>

                <button type="button" class="rv-tool-btn" onclick="navigator.clipboard.writeText(window.location.href); this.querySelector('span').textContent='Скопировано!'; setTimeout(()=>this.querySelector('span').textContent='Копировать ссылку', 1500)">
                    <i class="fa-solid fa-link"></i>
                    <span>Копировать ссылку</span>
                </button>

                @auth
                    @if(!$canEdit)
                        <a href="mailto:support@localhost?subject=Жалоба%20на%20обзор%20%23{{ $review->id }}" class="rv-tool-btn rv-tool-danger">
                            <i class="fa-solid fa-flag"></i>
                            <span>Пожаловаться</span>
                        </a>
                    @endif
                @endauth
            </div>

            <div class="rv-tools-right">
                @auth
                    @if($canEdit)
                        <a href="{{ route('reviews.edit', $review) }}" wire:navigate class="rv-tool-btn">
                            <i class="fa-solid fa-pen"></i><span>Редактировать</span>
                        </a>
                    @endif

                    @if($canModerate)
                        <form method="POST" action="{{ route('reviews.pin', $review) }}" class="rv-inline-form">
                            @csrf
                            <button type="submit" class="rv-tool-btn">
                                <i class="fa-solid fa-thumbtack"></i>
                                <span>{{ $review->is_pinned ? 'Открепить' : 'Закрепить' }}</span>
                            </button>
                        </form>
                    @endif

                    @if($canEdit)
                        <form method="POST" action="{{ route('reviews.destroy', $review) }}"
                              class="rv-inline-form"
                              onsubmit="return confirm('Удалить обзор?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="rv-tool-btn rv-tool-danger">
                                <i class="fa-solid fa-trash"></i><span>Удалить</span>
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>

        @if($review->novel)
            <div class="rv-cta-big">
                <a href="{{ route('novel.show', $review->novel->id) }}" wire:navigate class="eri-btn primary lg">
                    <i class="fa-solid fa-book-open"></i>
                    Перейти к новелле «{{ $review->novel->title }}»
                </a>
            </div>
        @endif
    </article>

    @if($prevReview || $nextReview)
        <div class="rv-siblings">
            <div class="rv-sibling-col">
                @if($prevReview)
                    <a href="{{ route('reviews.show', $prevReview) }}" wire:navigate class="rv-sibling">
                        <div class="rv-sibling-dir"><i class="fa-solid fa-chevron-left"></i> Предыдущий</div>
                        <div class="rv-sibling-title">{{ $prevReview->title }}</div>
                    </a>
                @endif
            </div>
            <div class="rv-sibling-col rv-sibling-col-right">
                @if($nextReview)
                    <a href="{{ route('reviews.show', $nextReview) }}" wire:navigate class="rv-sibling rv-sibling-right">
                        <div class="rv-sibling-dir">Следующий <i class="fa-solid fa-chevron-right"></i></div>
                        <div class="rv-sibling-title">{{ $nextReview->title }}</div>
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if($author && $authorOtherPosts->count())
        <section class="rv-author-card">
            <div class="rv-author-card-head">
                <a href="{{ route('users.show', $author->id) }}" wire:navigate class="rv-author-card-info">
                    @if($author->avatar_url)
                        <img src="{{ $author->avatar_url }}" alt="" class="rv-author-card-avatar">
                    @else
                        <span class="rv-author-card-avatar rv-author-card-avatar-text">{{ mb_strtoupper(mb_substr($author->name, 0, 1)) }}</span>
                    @endif
                    <div>
                        <div class="rv-author-card-name">{{ $author->name }}</div>
                        <div class="rv-author-card-sub">Другие обзоры этого автора</div>
                    </div>
                </a>
                <a href="{{ route('users.show', $author->id) }}" wire:navigate class="eri-btn">
                    Профиль →
                </a>
            </div>
            <div class="rv-author-card-posts">
                @foreach($authorOtherPosts as $other)
                    <a href="{{ route('reviews.show', $other) }}" wire:navigate class="rv-author-card-post">
                        <span class="rv-cat-mini rv-cat-{{ $other->category }}">[{{ $other->category_label }}]</span>
                        <span class="rv-author-card-post-title">{{ $other->title }}</span>
                        <span class="rv-author-card-post-meta">
                            <i class="fa-regular fa-eye"></i> {{ $other->views_count }} ·
                            <i class="fa-regular fa-thumbs-up"></i> {{ $other->recommends_count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="rv-comments">
        <div class="rv-comments-head">
            <h2 class="rv-comments-title">
                <i class="fa-regular fa-comments"></i>
                Всего комментариев: <span class="rv-comments-count">{{ $review->comments_count }}</span>
            </h2>
        </div>

        @livewire('comments-section', ['model' => $review])
    </section>
</div>

<script>
function rvShareReview(title, url) {
    title = decodeURIComponent(title);
    url = decodeURIComponent(url);
    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(() => {});
        return;
    }
    var menu = document.createElement('div');
    menu.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;';
    menu.innerHTML = '<div style="background:var(--surface);padding:24px;border-radius:12px;min-width:280px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">' +
        '<h3 style="margin:0 0 14px;font-family:var(--display);font-size:18px;">Поделиться</h3>' +
        '<a href="https://vk.com/share.php?url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title) + '" target="_blank" class="eri-btn block" style="margin-bottom:8px;">VK</a>' +
        '<a href="https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title) + '" target="_blank" class="eri-btn block" style="margin-bottom:8px;">Telegram</a>' +
        '<a href="https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title) + '" target="_blank" class="eri-btn block" style="margin-bottom:14px;">Twitter / X</a>' +
        '<button onclick="this.closest(\'.rv-share-modal\').remove()" class="eri-btn block">Закрыть</button>' +
        '</div>';
    menu.className = 'rv-share-modal';
    menu.onclick = function(e) { if (e.target === menu) menu.remove(); };
    document.body.appendChild(menu);
}
</script>
@endsection
