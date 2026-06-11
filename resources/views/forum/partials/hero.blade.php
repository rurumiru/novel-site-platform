
@php
    $snapshot = $initial ?? app(\App\Forum\Services\FeedService::class)->snapshot();
    $feedUrl  = route('forum.feed');
@endphp

<section
    class="forum-hero"
    data-forum-hero
    data-feed-url="{{ $feedUrl }}"
    data-poll-interval="20000"
    aria-label="Свежее на сайте"
>
    <header class="forum-hero__header">
        <div>
            <h1 class="forum-hero__title">
                <span class="forum-hero__sparkle"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                Что нового
            </h1>
            <p class="forum-hero__subtitle">Живая лента: новинки, топ и последние главы</p>
        </div>
        <div class="forum-hero__pulse" aria-hidden="true">
            <span class="forum-hero__pulse-dot"></span>
            <span class="forum-hero__pulse-label" data-hero-status>обновляется…</span>
        </div>
    </header>

    <div class="forum-hero__grid">
        
        <article class="forum-hero__col forum-hero__col--new">
            <header class="forum-hero__col-head">
                <h2><i class="fa-solid fa-sparkles"></i> Новинки</h2>
                <a href="{{ route('catalog') }}" class="forum-hero__col-more">все →</a>
            </header>
            <ul class="forum-hero__list" data-hero-list="new_novels">
                @foreach(($snapshot['new_novels'] ?? []) as $n)
                    <li class="forum-hero__item" data-id="{{ $n['id'] }}">
                        <a class="forum-hero__item-inner" href="{{ $n['url'] }}">
                            <span class="forum-hero__cover">
                                @if(!empty($n['cover']))
                                    <img src="{{ $n['cover'] }}" alt="" loading="lazy">
                                @else
                                    <span class="forum-hero__cover-fallback"><i class="fa-solid fa-book"></i></span>
                                @endif
                            </span>
                            <span class="forum-hero__meta">
                                <span class="forum-hero__name">{{ $n['title'] }}</span>
                                @if(!empty($n['author']))
                                    <span class="forum-hero__sub">{{ $n['author'] }}</span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="forum-hero__col forum-hero__col--top">
            <header class="forum-hero__col-head">
                <h2><i class="fa-solid fa-trophy"></i> Топ новелл</h2>
                <a href="{{ route('rankings') }}" class="forum-hero__col-more">рейтинг →</a>
            </header>
            <ul class="forum-hero__list" data-hero-list="top_novels">
                @foreach(($snapshot['top_novels'] ?? []) as $i => $n)
                    <li class="forum-hero__item" data-id="{{ $n['id'] }}">
                        <a class="forum-hero__item-inner" href="{{ $n['url'] }}">
                            <span class="forum-hero__rank">{{ $i + 1 }}</span>
                            <span class="forum-hero__meta">
                                <span class="forum-hero__name">{{ $n['title'] }}</span>
                                @if(!empty($n['rating']))
                                    <span class="forum-hero__sub">
                                        <i class="fa-solid fa-star"></i> {{ number_format($n['rating'], 1) }}
                                    </span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="forum-hero__col forum-hero__col--chapters">
            <header class="forum-hero__col-head">
                <h2><i class="fa-solid fa-clock-rotate-left"></i> Свежие главы</h2>
                <a href="{{ route('updates') }}" class="forum-hero__col-more">все →</a>
            </header>
            <ul class="forum-hero__list" data-hero-list="latest_chapters">
                @foreach(($snapshot['latest_chapters'] ?? []) as $c)
                    <li class="forum-hero__item" data-id="{{ $c['id'] }}">
                        <a class="forum-hero__item-inner" href="{{ $c['url'] }}">
                            <span class="forum-hero__cover forum-hero__cover--sm">
                                @if(!empty($c['cover']))
                                    <img src="{{ $c['cover'] }}" alt="" loading="lazy">
                                @else
                                    <span class="forum-hero__cover-fallback"><i class="fa-solid fa-file-lines"></i></span>
                                @endif
                            </span>
                            <span class="forum-hero__meta">
                                <span class="forum-hero__name">{{ $c['title'] }}</span>
                                <span class="forum-hero__sub">{{ $c['novel_title'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </article>
    </div>
</section>
