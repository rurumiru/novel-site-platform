@php $pageTitle = $section->name . ' — Форум'; @endphp
@extends('forum.layout')

@section('breadcrumbs')
    <span class="forum-bc-sep">/</span>
    <span class="forum-bc-current">{{ $section->name }}</span>
@endsection

@section('forum')
    <header class="forum-section-head">
        <div>
            <h1 class="forum-section-head__title">
                <i class="{{ $section->icon ?? 'fa-solid fa-comments' }}"></i>
                {{ $section->name }}
            </h1>
            @if($section->description)
                <p class="forum-section-head__desc">{{ $section->description }}</p>
            @endif
        </div>
        @if($canCreate)
            <a href="{{ route('forum.threads.create', $section) }}" class="forum-btn forum-btn--primary">
                <i class="fa-solid fa-plus"></i> Создать тему
            </a>
        @endif
    </header>

    <div class="forum-threads">
        @forelse($threads as $thread)
            <article class="forum-thread {{ $thread->is_pinned ? 'is-pinned' : '' }} {{ $thread->is_locked ? 'is-locked' : '' }}">
                <a href="{{ $thread->url() }}" class="forum-thread__link">
                    <span class="forum-thread__avatar">
                        <img src="{{ optional($thread->author)->avatar_url ?? '' }}" alt="" loading="lazy" onerror="this.style.display='none'">
                    </span>
                    <div class="forum-thread__body">
                        <div class="forum-thread__top">
                            @if($thread->is_pinned)
                                <span class="forum-thread__badge forum-thread__badge--pin"><i class="fa-solid fa-thumbtack"></i> закреплено</span>
                            @endif
                            @if($thread->is_locked)
                                <span class="forum-thread__badge forum-thread__badge--lock"><i class="fa-solid fa-lock"></i> закрыта</span>
                            @endif
                            @if($thread->visibility !== 'public')
                                <span class="forum-thread__badge forum-thread__badge--priv"><i class="fa-solid fa-eye-slash"></i> приватная</span>
                            @endif
                            <h2 class="forum-thread__title">{{ $thread->title }}</h2>
                        </div>
                        <div class="forum-thread__meta">
                            <span><i class="fa-solid fa-user"></i> {{ optional($thread->author)->name ?? '—' }}</span>
                            @if($thread->novel)
                                <span class="forum-thread__chip">
                                    <i class="fa-solid fa-book"></i> {{ $thread->novel->title }}
                                </span>
                            @endif
                            @foreach($thread->tags as $tag)
                                <span class="forum-thread__chip forum-thread__chip--tag">#{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="forum-thread__stats">
                        <span title="Сообщений"><i class="fa-solid fa-comment"></i> {{ $thread->posts_count }}</span>
                        <span title="Просмотров"><i class="fa-solid fa-eye"></i> {{ $thread->views_count }}</span>
                        <span class="forum-thread__last" title="Последняя активность">
                            <i class="fa-solid fa-clock"></i>
                            {{ optional($thread->last_post_at)->diffForHumans() }}
                        </span>
                    </div>
                </a>
            </article>
        @empty
            <div class="forum-empty">
                <p>В разделе ещё нет тем.</p>
                @if($canCreate)
                    <a href="{{ route('forum.threads.create', $section) }}" class="forum-btn forum-btn--primary">
                        Создать первую тему
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <div class="forum-pagination">
        {{ $threads->links() }}
    </div>
@endsection
