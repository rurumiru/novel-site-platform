@php
    $depth = $depth ?? 0;
    $maxDepth = 3;
    $u = $comment->user;
    $indent = min($depth, $maxDepth) * 28;
    $isReplying = (int) ($replyToId ?? 0) === (int) $comment->id;
    $isReporting = (int) ($reportingId ?? 0) === (int) $comment->id;

    $chapterRef = null;
    if (($comment->commentable_type ?? null) === \App\Models\Chapter::class) {
        $chapterRef = $comment->commentable;
    }

    $repliesCount = $comment->relationLoaded('replies') ? $comment->replies->count() : 0;
@endphp

<div class="comment-item" style="@if($depth > 0) margin-left:{{ $indent }}px; @endif"
     x-data="{ repliesOpen: true }">
    <div class="comment-card">
        <div class="comment-card-head">
            <a href="{{ $u ? route('users.show', $u->id) : '#' }}" class="comment-avatar-wrap" wire:navigate>
                <img src="{{ $u?->avatar_url ?? 'https://ui-avatars.com/api/?name=U' }}" alt=""
                     class="comment-avatar" style="width:{{ $depth === 0 ? 48 : 36 }}px;height:{{ $depth === 0 ? 48 : 36 }}px;">
            </a>
            <div class="comment-head-body">
                <div class="comment-name">
                    @if($u)
                        <a href="{{ route('users.show', $u->id) }}" wire:navigate class="comment-name-link">{{ $u->name }}</a>
                    @else
                        <span class="comment-name-link">—</span>
                    @endif
                    @if($u && $u->id === $authorId)
                        <span class="comment-badge author">Автор</span>
                    @endif
                    @if($u)
                        @foreach($u->getDisplayBadges() as $badge)
                            <span class="comment-badge" style="background:color-mix(in srgb, {{ $badge['color'] }} 14%, transparent); color:{{ $badge['color'] }};" title="{{ $badge['label'] }}">
                                <i class="fa-solid {{ $badge['icon'] }}" style="font-size:9px;"></i>
                            </span>
                        @endforeach
                    @endif
                </div>
                <div class="comment-meta-row">
                    <span class="comment-time">{{ $comment->created_at->format('d.m.y') }} написано</span>
                    @if($chapterRef)
                        <a href="{{ route('novel.read', [$chapterRef->novel_id, $chapterRef->id]) }}" wire:navigate class="comment-chapter-ref">
                            <i class="fa-solid fa-book" style="font-size:10px;"></i>
                            Глава: {{ $chapterRef->title }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="comment-body">
            {!! \App\Services\CommentContentRenderer::toSafeHtml($comment->content) ?: e($comment->content) !!}
        </div>

        <div class="comment-actions">
            @if($repliesCount > 0)
                <button type="button" @click="repliesOpen = !repliesOpen"
                        class="comment-action comment-action-replies"
                        :class="repliesOpen ? 'on' : ''"
                        title="Ответы">
                    <i class="fa-solid fa-chevron-up" :style="repliesOpen ? '' : 'transform:rotate(180deg);'"></i>
                    <span>Ответы ({{ $repliesCount }})</span>
                </button>
            @endif

            @auth
                @if($depth < $maxDepth)
                    <button type="button"
                            wire:click="{{ $isReplying ? 'cancelReply' : 'setReply(' . $comment->id . ')' }}"
                            class="comment-action comment-action-reply">
                        <i class="fa-regular fa-comment"></i>
                        <span>{{ $isReplying ? 'Отмена' : 'Ответить' }}</span>
                    </button>
                @endif
            @endauth

            <div class="comment-actions-right">
                <button type="button" wire:click="toggleRecommend({{ $comment->id }})"
                        class="comment-action comment-action-recommend @if($comment->is_recommended) on @endif"
                        title="Рекомендую">
                    <i class="{{ $comment->is_recommended ? 'fa-solid' : 'fa-regular' }} fa-thumbs-up"></i>
                    <span>Рек. ({{ $comment->recommendations_count ?? 0 }})</span>
                </button>
                <button type="button" wire:click="toggleLike({{ $comment->id }})"
                        class="comment-action comment-action-like @if($comment->is_liked) on @endif"
                        title="Нравится">
                    <i class="{{ $comment->is_liked ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                    <span>Нрав. ({{ $comment->likes_count ?? 0 }})</span>
                </button>
                @auth
                    <button type="button" wire:click="openReport({{ $comment->id }})"
                            class="comment-action comment-action-report"
                            title="Пожаловаться">
                        <i class="fa-regular fa-flag"></i>
                        <span>Жалоба</span>
                    </button>
                @endauth
            </div>
        </div>

        @auth
            @if($isReplying)
                <form wire:submit.prevent="postComment" class="comment-reply-form">
                    <textarea wire:model="content" rows="2" class="eri-textarea"
                              placeholder="Ответить {{ $u?->name ?? 'пользователю' }}..."
                              style="border-radius:var(--r-md);"></textarea>
                    @error('content') <div class="eri-error" style="margin-top:4px;">{{ $message }}</div> @enderror
                    <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px;">
                        <button type="button" wire:click="cancelReply" class="eri-btn ghost sm">Отмена</button>
                        <button type="submit" wire:loading.attr="disabled" class="eri-btn primary sm">
                            <span wire:loading.remove>Отправить</span>
                            <span wire:loading>...</span>
                        </button>
                    </div>
                </form>
            @endif

            @if($isReporting)
                <form wire:submit.prevent="submitReport" class="comment-reply-form" style="border-color:var(--err);">
                    <div style="font-size:12px; font-weight:600; color:var(--err); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.04em;">
                        <i class="fa-solid fa-flag"></i> Жалоба на сообщение
                    </div>
                    <select wire:model="reportReason" class="eri-select" style="margin-bottom:8px; border-radius:var(--r-md);">
                        <option value="">— причина —</option>
                        <option value="Оскорбления">Оскорбления / разжигание ненависти</option>
                        <option value="Спам">Спам / реклама</option>
                        <option value="Нарушение правил">Нарушение правил сайта</option>
                        <option value="Не по теме">Не по теме / флуд</option>
                        <option value="Спойлеры">Спойлеры без предупреждения</option>
                        <option value="Личные данные">Раскрытие личных данных</option>
                        <option value="Другое">Другое</option>
                    </select>
                    <textarea wire:model="reportDetails" rows="2" class="eri-textarea"
                              placeholder="Детали (необязательно)..."
                              style="border-radius:var(--r-md);"></textarea>
                    @error('reportReason') <div class="eri-error" style="margin-top:4px;">{{ $message }}</div> @enderror
                    <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px;">
                        <button type="button" wire:click="cancelReport" class="eri-btn ghost sm">Отмена</button>
                        <button type="submit" class="eri-btn sm" style="background:var(--err); color:#fff; border-color:var(--err);">
                            <i class="fa-solid fa-flag"></i> Отправить
                        </button>
                    </div>
                </form>
            @endif
        @endauth
    </div>

    @if($comment->relationLoaded('replies') && $comment->replies->isNotEmpty())
        <div class="comment-replies" x-show="repliesOpen" x-collapse>
            @foreach($comment->replies as $reply)
                @include('livewire.partials.comment-thread', [
                    'comment' => $reply,
                    'depth' => $depth + 1,
                    'replyToId' => $replyToId,
                    'reportingId' => $reportingId ?? null,
                    'authorId' => $authorId,
                ])
            @endforeach
        </div>
    @endif
</div>
