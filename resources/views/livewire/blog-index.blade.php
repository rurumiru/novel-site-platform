<div>
    
    <div class="blog-hero">
        <div class="blog-eyebrow">Блог · {{ now()->translatedFormat('F Y') }}</div>
        <h1>О книгах, авторах и платформе.</h1>
        <p>Редакционные эссе, интервью с авторами, гайды для читателей и обновления продукта. Один-два больших материала в неделю.</p>
    </div>

    @if($recruitmentPosts->count() > 0)
        <div class="blog-cats">
            <span class="lbl">Рубрики</span>
            <a href="{{ route('blog.index') }}" wire:navigate class="blog-cat on">Все</a>
            <a href="{{ route('recruitment.index') }}" wire:navigate class="blog-cat">Набор · {{ $recruitmentPosts->count() }}</a>
        </div>
    @endif

    @php
        $featured = $posts->first();
        $rest = $posts->slice(1);
    @endphp

    @if($featured)
        <div class="blog-featured">
            <a href="{{ route('blog.show', $featured->slug) }}" wire:navigate class="blog-featured-img">
                @if($featured->image)
                    <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}" loading="eager" decoding="async">
                @else
                    <x-eriiba.cover :src="null" :title="$featured->title" />
                @endif
            </a>
            <div class="blog-featured-meta">
                <span class="tag">★ материал недели</span>
                <h2><a href="{{ route('blog.show', $featured->slug) }}" wire:navigate style="color:inherit;text-decoration:none;">{{ $featured->title }}</a></h2>
                <p class="lede">{{ Str::limit(strip_tags(Str::markdown($featured->content)), 240) }}</p>
                <div class="byline">
                    <div class="av">
                        @if($featured->author?->avatar_url)
                            <img src="{{ $featured->author->avatar_url }}" alt="{{ $featured->author->name }}">
                        @else
                            {{ mb_strtoupper(mb_substr($featured->author?->name ?? 'E', 0, 2)) }}
                        @endif
                    </div>
                    <div>
                        <strong>{{ $featured->author?->name ?? 'eriiba' }}</strong>
                        <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);letter-spacing:0.04em;margin-top:2px;font-weight:600;">
                            {{ mb_strtoupper($featured->created_at->translatedFormat('d M')) }} · {{ $featured->views }} ПРОСМ.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="blog-layout">
        <main>
            <h2 class="blog-section-title">Свежее</h2>

            @if($rest->count() === 0 && !$featured)
                <p style="font-family:var(--serif);font-size:16px;color:var(--text-3);">Записей пока нет.</p>
            @else
                <div class="blog-grid">
                    @foreach($rest as $post)
                        @php
                            $tagClass = (isset($post->is_recruitment) && $post->is_recruitment) ? 'author' : 'editorial';
                            $tagLabel = (isset($post->is_recruitment) && $post->is_recruitment) ? 'Набор' : 'Редакция';
                        @endphp
                        <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="blog-card">
                            <div class="img">
                                @if($post->image)
                                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
                                @else
                                    <x-eriiba.cover :src="null" :title="$post->title" />
                                @endif
                            </div>
                            <span class="tag {{ $tagClass }}">{{ $tagLabel }}</span>
                            <h3>{{ $post->title }}</h3>
                            <p class="excerpt">{{ Str::limit(strip_tags(Str::markdown($post->content)), 160) }}</p>
                            <div class="meta">
                                <span class="author-name">{{ $post->author?->name ?? 'eriiba' }}</span>
                                <span>·</span>
                                <span>{{ mb_strtoupper($post->created_at->translatedFormat('d M')) }}</span>
                                <span>·</span>
                                <span>{{ $post->views }} ПРОСМ.</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div style="margin-top:32px;">{{ $posts->links() }}</div>
        </main>

        <aside class="blog-side">
            
            <div class="blog-side-card newsletter">
                <h3>Письма по средам</h3>
                <p>Один большой материал и три коротких — раз в неделю, без спама.</p>
                <form class="form" onsubmit="event.preventDefault();">
                    <input type="email" placeholder="вы@example.com">
                    <button type="submit" class="eri-btn primary sm">OK</button>
                </form>
                <div class="check">Можно отписаться в один клик.</div>
            </div>

            @php
                $popular = \App\Models\Post::where('is_published', true)->orderByDesc('views')->take(5)->get();
            @endphp
            @if($popular->count())
                <div class="blog-side-card">
                    <h3>Самое читаемое</h3>
                    @foreach($popular as $i => $p)
                        <a href="{{ route('blog.show', $p->slug) }}" wire:navigate class="popular-item">
                            <div class="num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <div>
                                <h4>{{ $p->title }}</h4>
                                <div class="meta">{{ $p->views }} ПРОСМОТРОВ · {{ mb_strtoupper($p->created_at->translatedFormat('d M')) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($recruitmentPosts->count() > 0)
                <div class="blog-side-card">
                    <h3>Открытые вакансии</h3>
                    @foreach($recruitmentPosts as $rp)
                        <a href="{{ route('recruitment.apply', $rp->slug) }}" wire:navigate class="popular-item">
                            <div class="num"><i class="fa-solid fa-user-tie" style="font-size:14px;color:var(--accent);"></i></div>
                            <div>
                                <h4>{{ $rp->title }}</h4>
                                <div class="meta">ОТКЛИКНУТЬСЯ →</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
</div>
