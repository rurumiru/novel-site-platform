@php $pageTitle = $thread->title; @endphp
@extends('forum.layout')

@section('breadcrumbs')
    <span class="forum-bc-sep">/</span>
    <a href="{{ route('forum.sections.show', $section) }}">{{ $section->name }}</a>
    <span class="forum-bc-sep">/</span>
    <span class="forum-bc-current">{{ \Illuminate\Support\Str::limit($thread->title, 60) }}</span>
@endsection

@section('forum')
    <article class="forum-thread-view">
        <header class="forum-thread-view__head">
            <div class="forum-thread-view__title-row">
                <h1 class="forum-thread-view__title">{{ $thread->title }}</h1>
                @auth
                    <div class="forum-thread-view__actions">
                        <button class="forum-icon-btn" data-forum-subscribe
                                data-url="{{ route('forum.threads.subscribe', [$section, $thread->slug]) }}"
                                data-active="{{ $isSubscribed ? '1' : '0' }}"
                                title="Подписаться на новые сообщения">
                            <i class="fa-{{ $isSubscribed ? 'solid' : 'regular' }} fa-bell"></i>
                        </button>
                        @can('update', $thread)
                            <a href="{{ route('forum.threads.edit', [$section, $thread->slug]) }}" class="forum-icon-btn" title="Редактировать">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        @endcan
                        @can('pin', \App\Forum\Models\Thread::class)
                            <form method="POST" action="{{ route('forum.threads.pin', [$section, $thread->slug]) }}">
                                @csrf
                                <button class="forum-icon-btn" title="{{ $thread->is_pinned ? 'Открепить' : 'Закрепить' }}">
                                    <i class="fa-solid fa-thumbtack {{ $thread->is_pinned ? 'is-on' : '' }}"></i>
                                </button>
                            </form>
                        @endcan
                        @can('lock', \App\Forum\Models\Thread::class)
                            <form method="POST" action="{{ route('forum.threads.lock', [$section, $thread->slug]) }}">
                                @csrf
                                <button class="forum-icon-btn" title="{{ $thread->is_locked ? 'Открыть' : 'Закрыть' }}">
                                    <i class="fa-solid fa-{{ $thread->is_locked ? 'lock-open' : 'lock' }}"></i>
                                </button>
                            </form>
                        @endcan
                    </div>
                @endauth
            </div>

            <div class="forum-thread-view__meta">
                <span><i class="fa-solid fa-user"></i> {{ optional($thread->author)->name ?? '—' }}</span>
                <span><i class="fa-solid fa-clock"></i> {{ optional($thread->created_at)->diffForHumans() }}</span>
                <span><i class="fa-solid fa-eye"></i> {{ $thread->views_count }}</span>
                <span><i class="fa-solid fa-comment"></i> {{ $thread->posts_count }}</span>
                @if($thread->novel)
                    <a class="forum-thread-view__chip" href="{{ route('novel.show', ['id' => $thread->novel->id]) }}">
                        <i class="fa-solid fa-book"></i> {{ $thread->novel->title }}
                    </a>
                @endif
                @if($thread->chapter)
                    <a class="forum-thread-view__chip"
                       href="{{ route('novel.read', ['novel_id' => $thread->chapter->novel_id, 'chapter_id' => $thread->chapter->id]) }}">
                        <i class="fa-solid fa-bookmark"></i> {{ $thread->chapter->title }}
                    </a>
                @endif
                @foreach($thread->tags as $t)
                    <span class="forum-thread-view__tag">#{{ $t->name }}</span>
                @endforeach
            </div>
        </header>

        <div class="forum-posts">
            @foreach($posts as $post)
                @include('forum.partials.post', ['post' => $post, 'thread' => $thread, 'section' => $section])
            @endforeach
        </div>

        <div class="forum-pagination">
            {{ $posts->links() }}
        </div>

        @if($thread->is_locked)
            <div class="forum-flash forum-flash--lock">
                <i class="fa-solid fa-lock"></i> Тема закрыта для новых сообщений.
            </div>
        @elseif($canReply)
            @include('forum.partials.reply-form', ['section' => $section, 'thread' => $thread])
        @else
            <div class="forum-flash forum-flash--info">
                @guest
                    <a href="{{ route('login') }}">Войдите</a>, чтобы ответить в этой теме.
                @else
                    У вас нет прав отвечать в этой теме.
                @endguest
            </div>
        @endif
    </article>
@endsection
