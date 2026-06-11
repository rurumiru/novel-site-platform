@php
    $authorId = null;
    if ($model instanceof \App\Models\Chapter) {
        $authorId = $model->novel?->user_id;
    } elseif ($model instanceof \App\Models\Novel) {
        $authorId = $model->user_id;
    } elseif (isset($model->user_id)) {
        $authorId = $model->user_id;
    }
@endphp
<div class="eri-comments">
    @auth
        @if(!$replyToId)
            <div class="eri-card" style="padding:18px;margin-bottom:24px;">
                <form wire:submit.prevent="postComment">
                    
                    <div class="comment-toolbar">
                        <button type="button" class="comment-tool" data-fmt-wrap="<strong>|</strong>" title="Жирный (Ctrl+B)">
                            <i class="fa-solid fa-bold"></i>
                        </button>
                        <button type="button" class="comment-tool" data-fmt-wrap="<em>|</em>" title="Курсив (Ctrl+I)">
                            <i class="fa-solid fa-italic"></i>
                        </button>
                        <button type="button" class="comment-tool" data-fmt-wrap="<u>|</u>" title="Подчёркнутый">
                            <i class="fa-solid fa-underline"></i>
                        </button>
                        <button type="button" class="comment-tool" data-fmt-wrap="<s>|</s>" title="Зачёркнутый">
                            <i class="fa-solid fa-strikethrough"></i>
                        </button>
                        <button type="button" class="comment-tool comment-tool-spoiler"
                                data-fmt-spoiler="1" title="Скрыть под спойлер">
                            🙈 <span class="comment-tool-label">Спойлер</span>
                        </button>
                    </div>
                    <textarea wire:model="content" class="eri-textarea comment-textarea-rich" rows="4"
                              placeholder="Поделитесь мыслями... (поддерживаются стикеры через 🙂 + теги &lt;strong&gt; &lt;em&gt; &lt;details&gt;)"
                              style="border-radius:0 0 var(--r-md) var(--r-md);border-top:0;"></textarea>
                    @error('content') <span class="eri-error">{{ $message }}</span> @enderror

                    @if($stickerPickerOpen ?? false)
                        <div class="eri-card eri-sticker-picker" style="margin-top:8px; padding:12px; max-height:280px; overflow-y:auto;">
                            @if(($stickerPacks ?? collect())->isEmpty())
                                <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px; font-family:var(--serif);">
                                    Стикеров пока нет.
                                </div>
                            @else
                                @foreach($stickerPacks as $pack)
                                    <div style="margin-bottom:14px;">
                                        <div class="eri-label" style="margin-bottom:8px;">{{ $pack->name }}</div>
                                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(64px, 1fr)); gap:6px;">
                                            @foreach($pack->stickers as $st)
                                                <button type="button" wire:click="insertSticker({{ $st->id }})"
                                                        style="padding:4px; background:none; border:1px solid var(--border);
                                                               border-radius:var(--r-sm); cursor:pointer;
                                                               transition:transform 0.1s, border-color 0.1s;"
                                                        onmouseover="this.style.borderColor='var(--accent)'; this.style.transform='scale(1.05)';"
                                                        onmouseout="this.style.borderColor='var(--border)'; this.style.transform='';"
                                                        title="{{ $st->name ?? $st->slug }}">
                                                    <img src="{{ $st->url }}" alt="{{ $st->name }}"
                                                         style="width:100%; aspect-ratio:1; object-fit:contain;" loading="lazy">
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                        <div style="display:flex; gap:6px; align-items:center;">
                            <button type="button" wire:click="toggleStickerPicker"
                                    class="eri-btn ghost sm" title="Стикеры">
                                <i class="fa-regular fa-face-smile"></i>
                            </button>
                            <span style="font-size:11px;color:var(--text-muted);">
                                <i class="fa-regular fa-circle-info"></i> Будьте уважительны
                            </span>
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="eri-btn primary">
                            <span wire:loading.remove>Отправить</span>
                            <span wire:loading>...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @else
        <div class="eri-card" style="padding:24px;text-align:center;margin-bottom:24px;">
            <p style="color:var(--text-2);margin:0 0 14px;font-family:var(--serif);">
                Чтобы оставить комментарий, нужно войти в аккаунт.
            </p>
            <div style="display:flex;gap:8px;justify-content:center;">
                <a href="{{ route('login') }}" wire:navigate class="eri-btn primary">Войти</a>
                <a href="{{ route('register') }}" wire:navigate class="eri-btn">Регистрация</a>
            </div>
        </div>
    @endauth

    @if(session('comment_report_ok'))
        <div class="eri-alert ok" style="margin-bottom:14px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('comment_report_ok') }}
        </div>
    @endif

    <div class="comments-sort-bar">
        <div class="comments-count">
            Всего <strong>{{ $comments->count() }}</strong>
        </div>
        <div class="comments-sort-options">
            <button type="button" wire:click="setSort('latest')"
                    class="comments-sort-btn @if($sortBy === 'latest') on @endif">
                <i class="fa-solid fa-circle"></i> Новые
            </button>
            <button type="button" wire:click="setSort('oldest')"
                    class="comments-sort-btn @if($sortBy === 'oldest') on @endif">
                <i class="fa-solid fa-circle"></i> Старые
            </button>
            <button type="button" wire:click="setSort('recommended')"
                    class="comments-sort-btn @if($sortBy === 'recommended') on @endif">
                <i class="fa-solid fa-circle"></i> Рекомендуемые
            </button>
        </div>
    </div>

    <script>
    (function(){
        if (window.__rvCommentToolbarReady) return;
        window.__rvCommentToolbarReady = true;

        function wrapInTextarea(ta, before, after) {
            var start = ta.selectionStart, end = ta.selectionEnd;
            var sel = ta.value.substring(start, end);
            var insert = before + sel + after;
            ta.setRangeText(insert, start, end, 'end');
            ta.focus();
            ta.dispatchEvent(new Event('input', { bubbles: true }));
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.comment-tool');
            if (!btn) return;
            e.preventDefault();
            var ta = btn.closest('form')?.querySelector('textarea.comment-textarea-rich');
            if (!ta) return;

            if (btn.dataset.fmtSpoiler) {
                var label = prompt('Текст ярлыка спойлера (по умолчанию «Спойлер»):', 'Спойлер');
                if (label === null) return;
                if (!label) label = 'Спойлер';
                wrapInTextarea(ta, '<details><summary>' + label + '</summary>', '</details>');
                return;
            }
            var wrap = btn.dataset.fmtWrap;
            if (!wrap) return;
            var parts = wrap.split('|');
            if (parts.length !== 2) return;
            wrapInTextarea(ta, parts[0], parts[1]);
        });

        // Ctrl/Cmd+B и Ctrl/Cmd+I
        document.addEventListener('keydown', function (e) {
            if (!(e.ctrlKey || e.metaKey)) return;
            var ta = e.target;
            if (!(ta instanceof HTMLTextAreaElement)) return;
            if (!ta.classList.contains('comment-textarea-rich')) return;
            var key = e.key.toLowerCase();
            if (key === 'b') { e.preventDefault(); wrapInTextarea(ta, '<strong>', '</strong>'); }
            else if (key === 'i') { e.preventDefault(); wrapInTextarea(ta, '<em>', '</em>'); }
        });
    })();
    </script>

    <div class="comment-list">
        @forelse($comments as $comment)
            @include('livewire.partials.comment-thread', [
                'comment' => $comment,
                'depth' => 0,
                'replyToId' => $replyToId,
                'reportingId' => $reportingId,
                'authorId' => $authorId,
            ])
        @empty
            <div style="text-align:center;padding:36px 20px;color:var(--text-muted);font-style:italic;font-family:var(--serif);">
                Здесь пока тихо. Будьте первым!
            </div>
        @endforelse
    </div>
</div>
