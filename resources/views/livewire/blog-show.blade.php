<div>
    <div class="eri-section" style="padding-top:24px;padding-bottom:0;">
        <a href="{{ route('blog.index') }}" wire:navigate
           style="font-family:var(--mono);font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-3);text-decoration:none;font-weight:600;">
            ← Назад в блог
        </a>
    </div>

    <article class="eri-section" style="padding-top:18px;">
        
        <header style="max-width:1400px;margin:0 auto 28px;">
            <div style="font-family:var(--mono);font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:var(--accent);font-weight:700;margin-bottom:14px;">
                @if(isset($post->is_recruitment) && $post->is_recruitment)
                    Набор · {{ $post->created_at->translatedFormat('d F Y') }}
                @else
                    Блог · {{ $post->created_at->translatedFormat('d F Y') }}
                @endif
            </div>
            <h1 style="font-family:var(--display);font-size:clamp(34px,5vw,56px);font-weight:500;letter-spacing:-0.025em;line-height:1.02;margin:0 0 18px;color:var(--text);text-wrap:balance;">
                {{ $post->title }}
            </h1>
            <div style="display:flex;align-items:center;gap:12px;font-size:13px;color:var(--text-3);">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#c067a0,#6b3fa0);color:#fff;display:grid;place-items:center;font-weight:700;font-size:13px;overflow:hidden;">
                    @if($post->author?->avatar_url)
                        <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ mb_strtoupper(mb_substr($post->author?->name ?? 'E', 0, 2)) }}
                    @endif
                </div>
                <div>
                    <strong style="color:var(--text);font-weight:600;">{{ $post->author?->name ?? 'eriiba' }}</strong>
                    <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);letter-spacing:0.04em;margin-top:2px;font-weight:600;">
                        {{ mb_strtoupper($post->created_at->translatedFormat('d M Y')) }} · {{ $post->views }} ПРОСМ.
                    </div>
                </div>
            </div>
        </header>

        @if($post->image)
            <figure style="max-width:1400px;margin:0 auto 36px;">
                <div style="aspect-ratio:16/9;border-radius:var(--r-lg);overflow:hidden;background:var(--surface-3);">
                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;"
                         fetchpriority="high" loading="eager" decoding="async">
                </div>
            </figure>
        @endif

        @if(isset($post->is_recruitment) && $post->is_recruitment)
            <div style="max-width:1400px;margin:0 auto 28px;">
                <a href="{{ route('recruitment.apply', $post->slug) }}" wire:navigate class="eri-btn primary block">
                    <i class="fa-solid fa-paper-plane"></i> Откликнуться на вакансию
                </a>
            </div>
        @endif

        <div class="serif" style="max-width:1400px;margin:0 auto;font-family:var(--serif);font-size:19px;line-height:1.7;color:var(--text);">
            <style scoped>
                .serif h2 { font-family: var(--display); font-size: 32px; font-weight: 500; letter-spacing: -0.02em; line-height: 1.15; margin: 44px 0 16px; color: var(--text); }
                .serif h3 { font-family: var(--display); font-size: 24px; font-weight: 500; letter-spacing: -0.015em; line-height: 1.2; margin: 32px 0 12px; color: var(--text); }
                .serif h4 { font-family: var(--display); font-size: 20px; font-weight: 500; letter-spacing: -0.01em; margin: 26px 0 10px; color: var(--text); }
                .serif p { margin: 0 0 22px; text-wrap: pretty; }
                .serif a { color: var(--accent); text-decoration: underline; text-underline-offset: 3px; text-decoration-thickness: 1px; }
                .serif a:hover { color: var(--accent-strong, var(--accent)); }
                .serif blockquote { margin: 28px 0; padding: 8px 22px; border-left: 3px solid var(--accent); font-style: italic; color: var(--text-2); }
                .serif ul, .serif ol { margin: 0 0 22px; padding-left: 24px; }
                .serif li { margin-bottom: 8px; }
                .serif code { font-family: var(--mono); font-size: 0.88em; background: var(--surface-2); padding: 2px 6px; border-radius: var(--r-xs); }
                .serif pre { font-family: var(--mono); font-size: 14px; background: var(--surface-2); padding: 16px 18px; border-radius: var(--r-sm); overflow-x: auto; margin: 0 0 22px; }
                .serif pre code { background: none; padding: 0; font-size: inherit; }
                .serif img { max-width: 100%; height: auto; border-radius: var(--r-sm); margin: 22px 0; display: block; }
                .serif hr { border: 0; border-top: 1px solid var(--border); margin: 36px 0; }
                .serif strong { color: var(--text); font-weight: 600; }
            </style>
            {!! Str::markdown($post->content) !!}
        </div>
    </article>

    <div class="eri-section" style="padding-top:8px;">
        <div style="max-width:1400px;margin:0 auto;">
            <h2 style="font-family:var(--display);font-size:24px;font-weight:500;letter-spacing:-0.02em;margin:0 0 18px;color:var(--text);padding-bottom:12px;border-bottom:1px solid var(--border);">
                Комментарии
            </h2>
            @livewire('comments-section', ['model' => $post])
        </div>
    </div>
</div>
