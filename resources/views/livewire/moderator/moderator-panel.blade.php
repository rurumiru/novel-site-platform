<div class="editor-shell">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;margin-bottom:24px;">
        <div>
            <h1 style="font-family:var(--display);font-size:32px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0;display:inline-flex;align-items:center;gap:12px;">
                <span style="width:40px;height:40px;border-radius:var(--r-sm);background:var(--accent-soft);color:var(--accent);display:grid;place-items:center;font-size:18px;">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                Панель модератора
            </h1>
            <p style="font-size:13px;color:var(--text-3);margin:6px 0 0 52px;">Модерация новелл, комментариев и сообщений</p>
        </div>
        @if(!Auth::user()->hasRole('super_admin'))
            <form action="{{ route('moderator.logout') }}" method="POST">
                @csrf
                <x-eriiba.btn type="submit" variant="ghost" icon="lock">Выйти из модерации</x-eriiba.btn>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="eri-card" style="background:var(--tag-slice-bg);color:var(--tag-slice-fg);border-color:transparent;margin-bottom:18px;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    <div class="eri-tabs">
        <button wire:click="$set('tab', 'novels')" class="eri-tab {{ $tab === 'novels' ? 'on' : '' }}"><i class="fa-solid fa-book-open"></i> Новеллы</button>
        <button wire:click="$set('tab', 'comments')" class="eri-tab {{ $tab === 'comments' ? 'on' : '' }}"><i class="fa-solid fa-comments"></i> Комментарии</button>
        <button wire:click="$set('tab', 'messages')" class="eri-tab {{ $tab === 'messages' ? 'on' : '' }}"><i class="fa-solid fa-flag"></i> Жалобы на спам</button>
    </div>

    @if($tab === 'novels')
        <div class="editor-section" style="padding:0;">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Поиск по названию, автору..." class="eri-input" style="flex:1;min-width:240px;">
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <button wire:click="$set('novelFilter', 'all')" class="eri-btn sm {{ $novelFilter === 'all' ? 'primary' : '' }}">Все</button>
                    <button wire:click="$set('novelFilter', 'pending')" class="eri-btn sm {{ $novelFilter === 'pending' ? 'primary' : '' }}">На проверке</button>
                    <button wire:click="$set('novelFilter', 'approved')" class="eri-btn sm {{ $novelFilter === 'approved' ? 'primary' : '' }}">Одобрено</button>
                    <button wire:click="$set('novelFilter', 'rejected')" class="eri-btn sm {{ $novelFilter === 'rejected' ? 'primary' : '' }}">Отклонено</button>
                    <button wire:click="$set('novelFilter', 'content')" class="eri-btn sm {{ $novelFilter === 'content' ? 'primary' : '' }}">Обложки/Описание</button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%;font-size:13px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--surface-2);">
                            <th style="text-align:left;padding:12px 16px;font-weight:600;color:var(--text-2);font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">Новелла</th>
                            <th style="text-align:left;padding:12px 16px;font-weight:600;color:var(--text-2);font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">Автор</th>
                            <th style="text-align:left;padding:12px 16px;font-weight:600;color:var(--text-2);font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">Статус</th>
                            <th style="text-align:left;padding:12px 16px;font-weight:600;color:var(--text-2);font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">Глав</th>
                            <th style="text-align:right;padding:12px 16px;font-weight:600;color:var(--text-2);font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($novels as $novel)
                            <tr style="border-top:1px solid var(--border);">
                                <td style="padding:14px 16px;">
                                    <a href="{{ route('novel.show', $novel->id) }}" wire:navigate style="font-family:var(--display);font-size:15px;font-weight:500;color:var(--text);text-decoration:none;letter-spacing:-0.01em;">{{ $novel->title }}</a>
                                    @php $cStat = $novel->cover_moderation_status; $dStat = $novel->description_moderation_status; @endphp
                                    @if($cStat === 'pending' || $dStat === 'pending')
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;align-items:center;">
                                            @if($cStat === 'pending' && $novel->cover_image)
                                                <img src="{{ \App\Models\Novel::storageUrl($novel->cover_image) }}" alt="" style="width:32px;height:44px;object-fit:cover;border-radius:var(--r-xs);">
                                                <button wire:click="approveCover({{ $novel->id }})" class="eri-chip slice" style="cursor:pointer;border:0;">Обложка ✓</button>
                                                <button wire:click="rejectCover({{ $novel->id }})" class="eri-chip danger" style="cursor:pointer;border:0;">Обложка ✗</button>
                                            @endif
                                            @if($dStat === 'pending')
                                                <button wire:click="approveDescription({{ $novel->id }})" class="eri-chip slice" style="cursor:pointer;border:0;">Описание ✓</button>
                                                <button wire:click="rejectDescription({{ $novel->id }})" class="eri-chip danger" style="cursor:pointer;border:0;">Описание ✗</button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;color:var(--text-3);">{{ $novel->publisher?->name ?? $novel->author_name ?? '—' }}</td>
                                <td style="padding:14px 16px;">
                                    @php
                                        $status = $novel->moderation_status ?? 'approved';
                                        $sVar = match($status) { 'pending' => 'warn', 'approved' => 'slice', 'rejected' => 'danger', default => '' };
                                        $sLabel = match($status) { 'pending' => 'На проверке', 'approved' => 'Одобрено', 'rejected' => 'Отклонено', default => $status };
                                    @endphp
                                    <span class="eri-chip {{ $sVar }}">{{ $sLabel }}</span>
                                    @if($novel->is_published)
                                        <span class="eri-chip accent" style="margin-left:4px;">Опубликовано</span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;color:var(--text-2);font-family:var(--mono);">{{ $novel->chapters_count }}</td>
                                <td style="padding:14px 16px;text-align:right;">
                                    <div style="display:flex;flex-wrap:wrap;justify-content:flex-end;gap:6px;">
                                        <a href="{{ route('author.novel.edit', $novel->id) }}" wire:navigate class="eri-btn sm">Редактировать</a>
                                        @if($status === 'pending' || !$novel->is_published)
                                            <button wire:click="approveNovel({{ $novel->id }})" wire:confirm="Одобрить и опубликовать?" class="eri-btn sm primary">Одобрить</button>
                                            <button wire:click="rejectNovel({{ $novel->id }})" wire:confirm="Отклонить?" class="eri-btn sm danger">Отклонить</button>
                                        @endif
                                        @if($novel->is_published)
                                            <button wire:click="unpublishNovel({{ $novel->id }})" wire:confirm="Снять с публикации?" class="eri-btn sm" style="background:var(--tag-action-bg);color:var(--tag-action-fg);border-color:transparent;">Снять</button>
                                        @else
                                            <button wire:click="publishNovel({{ $novel->id }})" class="eri-btn sm primary">Опубликовать</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="padding:36px;text-align:center;color:var(--text-muted);">Нет новелл</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--border);">{{ $novels->links() }}</div>
        </div>
    @endif

    @if($tab === 'comments')
        <div class="editor-section" style="padding:0;">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);">
                <h2 style="font-family:var(--display);font-size:18px;font-weight:500;color:var(--text);margin:0;">Последние комментарии</h2>
            </div>
            <div>
                @forelse($comments as $comment)
                    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:flex-start;gap:14px;">
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                                <span style="font-weight:600;color:var(--text);font-size:13px;">{{ $comment->user?->name ?? 'Удалён' }}</span>
                                <span style="font-size:11px;color:var(--text-muted);font-family:var(--mono);">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p style="font-family:var(--serif);font-size:14px;line-height:1.55;color:var(--text-2);margin:0;">{{ Str::limit($comment->content, 200) }}</p>
                            @if($comment->commentable)
                                <a href="{{ $comment->commentable_type === 'App\Models\Novel' ? route('novel.show', $comment->commentable_id) : '#' }}" wire:navigate style="font-size:11px;color:var(--accent);text-decoration:none;margin-top:6px;display:inline-block;">
                                    {{ $comment->commentable_type === 'App\Models\Novel' ? 'Новелла' : 'Глава' }} →
                                </a>
                            @endif
                        </div>
                        <button wire:click="deleteComment({{ $comment->id }})" wire:confirm="Удалить комментарий?" class="eri-btn sm danger">Удалить</button>
                    </div>
                @empty
                    <div style="padding:36px;text-align:center;color:var(--text-muted);">Комментариев нет</div>
                @endforelse
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--border);">{{ $comments->links() }}</div>
        </div>
    @endif

    @if($tab === 'messages')
        <div class="editor-section" style="padding:0;">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);">
                <h2 style="font-family:var(--display);font-size:18px;font-weight:500;color:var(--text);margin:0 0 4px;">Жалобы на спам в сообщениях</h2>
                <p style="font-size:12px;color:var(--text-3);margin:0;">Сообщения, на которые пожаловались пользователи</p>
            </div>
            <div>
                @forelse($spamMessages as $msg)
                    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:flex-start;gap:14px;">
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                                <span style="font-weight:600;color:var(--text);font-size:13px;">{{ $msg->sender?->name ?? 'Удалён' }}</span>
                                <span style="color:var(--text-muted);">→</span>
                                <span style="font-weight:600;color:var(--text);font-size:13px;">{{ $msg->receiver?->name ?? 'Удалён' }}</span>
                                <span style="font-size:11px;color:var(--text-muted);font-family:var(--mono);">{{ $msg->spam_reported_at?->diffForHumans() ?? '' }}</span>
                                @if($msg->reporter)
                                    <span class="eri-chip warn">Пожаловался: {{ $msg->reporter->name }}</span>
                                @endif
                            </div>
                            <p style="font-family:var(--serif);font-size:14px;line-height:1.55;color:var(--text-2);margin:0;background:var(--surface-2);padding:10px 14px;border-radius:var(--r-sm);">{{ Str::limit($msg->message, 300) }}</p>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <button wire:click="dismissSpamMessage({{ $msg->id }})" class="eri-btn sm">Снять жалобу</button>
                            <button wire:click="deleteSpamMessage({{ $msg->id }})" wire:confirm="Удалить сообщение?" class="eri-btn sm danger">Удалить</button>
                        </div>
                    </div>
                @empty
                    <div style="padding:36px;text-align:center;color:var(--text-muted);">Жалоб на спам нет</div>
                @endforelse
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--border);">{{ $spamMessages->links() }}</div>
        </div>
    @endif
</div>
