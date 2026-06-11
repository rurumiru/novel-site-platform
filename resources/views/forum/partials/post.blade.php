
@php
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canEdit  = auth()->check() && auth()->user()->can('update', $post);
    $canDelete = auth()->check() && auth()->user()->can('delete', $post);
    $canReact = auth()->check() && auth()->user()->can('react', $post);
@endphp

<article class="forum-post {{ $post->is_first ? 'is-first' : '' }}" id="post-{{ $post->id }}">
    <aside class="forum-post__aside">
        <a href="{{ route('users.show', ['id' => $post->user_id]) }}" class="forum-post__avatar">
            <img src="{{ optional($post->author)->avatar_url ?? '' }}" alt="" loading="lazy" onerror="this.style.display='none'">
        </a>
        <div class="forum-post__author">
            <a href="{{ route('users.show', ['id' => $post->user_id]) }}">{{ optional($post->author)->name ?? '—' }}</a>
            @if(optional($post->author)->hasRole('author'))
                <span class="forum-post__role" title="Автор">A</span>
            @endif
        </div>
        <time class="forum-post__time" datetime="{{ $post->created_at?->toIso8601String() }}">
            {{ $post->created_at?->diffForHumans() }}
        </time>
    </aside>

    <div class="forum-post__main">
        @if($post->parent)
            <div class="forum-post__quote">
                <i class="fa-solid fa-quote-left"></i>
                <a href="#post-{{ $post->parent->id }}">{{ optional($post->parent->author)->name }}</a>
                писал(а):
                <blockquote>{{ \Illuminate\Support\Str::limit(strip_tags($post->parent->body_html ?? $post->parent->body), 200) }}</blockquote>
            </div>
        @endif

        <div class="forum-post__body forum-md" data-post-body>
            {!! $post->body_html !!}
        </div>

        @if($post->edited_at)
            <div class="forum-post__edited">
                <i class="fa-solid fa-pen"></i> отредактировано {{ $post->edited_at->diffForHumans() }}
            </div>
        @endif

        <footer class="forum-post__footer">
            <div class="forum-post__reactions" data-post-reactions="{{ $post->id }}">
                @php
                    $breakdown = $post->reactions->groupBy('emoji')->map->count();
                    $myReactions = auth()->check()
                        ? $post->reactions->where('user_id', auth()->id())->pluck('emoji')->all()
                        : [];
                @endphp
                @foreach(\App\Forum\Models\Reaction::ALLOWED as $emoji)
                    @php
                        $count = $breakdown[$emoji] ?? 0;
                        $mine  = in_array($emoji, $myReactions, true);
                    @endphp
                    <button
                        type="button"
                        class="forum-react {{ $mine ? 'is-on' : '' }} {{ $count === 0 ? 'is-empty' : '' }}"
                        data-emoji="{{ $emoji }}"
                        data-url="{{ route('forum.posts.react', $post) }}"
                        {{ $canReact ? '' : 'disabled' }}
                        title="{{ ucfirst($emoji) }}">
                        <span class="forum-react__icon forum-react__icon--{{ $emoji }}"></span>
                        <span class="forum-react__count">{{ $count > 0 ? $count : '' }}</span>
                    </button>
                @endforeach
            </div>
            <div class="forum-post__tools">
                @auth
                    <button type="button" class="forum-link-btn" data-forum-quote="{{ $post->id }}">
                        <i class="fa-solid fa-reply"></i> Ответить
                    </button>
                @endauth
                @if($canEdit)
                    <button type="button" class="forum-link-btn" data-forum-edit-post="{{ $post->id }}"
                            data-source="{{ e($post->body) }}">
                        <i class="fa-solid fa-pen"></i> Изм.
                    </button>
                @endif
                @if($canDelete)
                    <form method="POST" action="{{ route('forum.posts.destroy', $post) }}"
                          onsubmit="return confirm('Удалить сообщение?')" class="forum-inline">
                        @csrf @method('DELETE')
                        <button class="forum-link-btn forum-link-btn--danger">
                            <i class="fa-solid fa-trash"></i> Удалить
                        </button>
                    </form>
                @endif
                @auth
                    <button type="button" class="forum-link-btn" data-forum-report-post="{{ $post->id }}"
                            data-url="{{ route('forum.posts.report', $post) }}">
                        <i class="fa-solid fa-flag"></i> Пожаловаться
                    </button>
                @endauth
            </div>
        </footer>
    </div>
</article>
