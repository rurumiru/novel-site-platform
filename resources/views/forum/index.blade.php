@php $pageTitle = 'Форум'; @endphp
@extends('forum.layout')

@section('forum')
    <div class="forum-grid forum-grid--cat">
        <section class="forum-categories">
            @forelse($categories as $cat)
                <div class="forum-cat" style="--cat-accent: {{ $cat->color ?? 'var(--accent)' }}">
                    <header class="forum-cat__head">
                        <span class="forum-cat__icon"><i class="{{ $cat->icon ?? 'fa-solid fa-folder' }}"></i></span>
                        <div>
                            <h2 class="forum-cat__name">{{ $cat->name }}</h2>
                            @if($cat->description)
                                <p class="forum-cat__desc">{{ $cat->description }}</p>
                            @endif
                        </div>
                    </header>

                    <ul class="forum-sections">
                        @foreach($cat->activeSections as $sec)
                            <li class="forum-section">
                                <a href="{{ route('forum.sections.show', $sec) }}" class="forum-section__link">
                                    <span class="forum-section__icon" style="color: {{ $sec->accent_color ?? $cat->color ?? 'var(--accent)' }}">
                                        <i class="{{ $sec->icon ?? 'fa-solid fa-comments' }}"></i>
                                    </span>
                                    <span class="forum-section__body">
                                        <span class="forum-section__name">{{ $sec->name }}</span>
                                        @if($sec->description)
                                            <span class="forum-section__desc">{{ $sec->description }}</span>
                                        @endif
                                    </span>
                                    <span class="forum-section__meta">
                                        <span class="forum-section__count">{{ $sec->threads_count ?? 0 }}</span>
                                        <span class="forum-section__count-label">тем</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="forum-empty">Категории форума пока не настроены.</div>
            @endforelse
        </section>

        <aside class="forum-side">
            <div class="forum-side__card">
                <h3 class="forum-side__title"><i class="fa-solid fa-bolt"></i> Последняя активность</h3>
                @if($recentThreads->isEmpty())
                    <p class="forum-side__empty">Пока тихо. Будь первым — начни обсуждение.</p>
                @else
                    <ul class="forum-side__list">
                        @foreach($recentThreads as $t)
                            <li class="forum-side__item">
                                <a href="{{ $t->url() }}" class="forum-side__thread">
                                    <span class="forum-side__thread-title">{{ $t->title }}</span>
                                    <span class="forum-side__thread-meta">
                                        <span>{{ $t->section?->name }}</span>
                                        <span>·</span>
                                        <span>{{ optional($t->last_post_at)->diffForHumans() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </aside>
    </div>
@endsection
