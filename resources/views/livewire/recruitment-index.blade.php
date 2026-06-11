<div>
    <div class="blog-hero">
        <div class="blog-eyebrow">Команда eriiba</div>
        <h1>Набор модераторов и работников.</h1>
        <p>Открытые вакансии для тех, кто хочет помочь нам делать платформу лучше. Откройте описание и откликнитесь через короткую анкету.</p>
    </div>

    <div class="eri-section">
        @if($posts->isEmpty())
            <div class="eri-card eri-card-pad-lg" style="text-align:center;max-width:560px;margin:0 auto;">
                <div style="font-size:36px;color:var(--text-faint);margin-bottom:12px;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h2 style="font-family:var(--display);font-size:22px;font-weight:500;letter-spacing:-0.015em;margin:0 0 8px;color:var(--text);">
                    Сейчас открытых вакансий нет
                </h2>
                <p style="font-family:var(--serif);font-size:15px;color:var(--text-3);margin:0 0 22px;">
                    Подпишитесь на блог — мы публикуем вакансии там же.
                </p>
                <a href="{{ route('blog.index') }}" wire:navigate class="eri-btn primary">Перейти в блог</a>
            </div>
        @else
            <div class="eri-section-head">
                <h2>Открытые вакансии</h2>
                <span class="sub">{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('вакансия', $posts->total()) }}</span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px;">
                @foreach($posts as $post)
                    <div class="eri-card" style="display:flex;flex-direction:column;gap:14px;">
                        @if($post->image)
                            <a href="{{ route('blog.show', $post->slug) }}" wire:navigate
                               style="display:block;aspect-ratio:16/9;border-radius:var(--r-sm);overflow:hidden;background:var(--surface-3);">
                                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async"
                                     style="width:100%;height:100%;object-fit:cover;display:block;">
                            </a>
                        @endif

                        <div>
                            <span class="eri-chip warn" style="margin-bottom:10px;">Набор</span>
                            <h3 style="font-family:var(--display);font-size:20px;font-weight:500;letter-spacing:-0.015em;line-height:1.2;margin:8px 0 8px;color:var(--text);text-wrap:balance;">
                                <a href="{{ route('blog.show', $post->slug) }}" wire:navigate style="color:inherit;text-decoration:none;">{{ $post->title }}</a>
                            </h3>
                            <p style="font-family:var(--serif);font-size:14px;line-height:1.55;color:var(--text-2);margin:0;">
                                {{ Str::limit(strip_tags(Str::markdown($post->content)), 140) }}
                            </p>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:auto;">
                            <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="eri-btn sm" style="flex:1;text-align:center;">Подробнее</a>
                            <a href="{{ route('recruitment.apply', $post->slug) }}" wire:navigate class="eri-btn primary sm" style="flex:1;text-align:center;">Откликнуться</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:32px;">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
