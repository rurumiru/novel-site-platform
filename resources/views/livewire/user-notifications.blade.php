<div class="eri-section" style="max-width:760px;">
    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:18px;gap:12px;flex-wrap:wrap;">
        <h1 style="font-family:var(--display);font-size:32px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0;">Уведомления</h1>
        @if(($counts[$tab] ?? 0) > 0)
            <button wire:click="markAllAsRead" class="eri-btn ghost sm">
                <i class="fa-solid fa-check-double"></i> Прочитать все
            </button>
        @endif
    </div>

    <div class="eri-tabs" style="margin-bottom:18px;flex-wrap:wrap;">
        @foreach([
            'all'      => ['label' => 'Все',        'icon' => 'fa-bell'],
            'comments' => ['label' => 'Комментарии','icon' => 'fa-comments'],
            'errors'   => ['label' => 'Ошибки',     'icon' => 'fa-triangle-exclamation'],
            'updates'  => ['label' => 'Главы',      'icon' => 'fa-book-open'],
            'messages' => ['label' => 'Сообщения',  'icon' => 'fa-envelope'],
        ] as $tKey => $tMeta)
            <button wire:click="setTab('{{ $tKey }}')" class="eri-tab {{ $tab === $tKey ? 'on' : '' }}">
                <i class="fa-solid {{ $tMeta['icon'] }}"></i> {{ $tMeta['label'] }}
                @if(($counts[$tKey] ?? 0) > 0)
                    <span class="eri-chip {{ $tab === $tKey ? 'accent' : '' }}" style="margin-left:4px;font-size:10px;padding:1px 7px;">{{ $counts[$tKey] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <div style="display:grid;gap:10px;">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $type = $data['type'] ?? '';
                $isRead = !!$notification->read_at;
                $iconMap = [
                    'chapter_error'  => ['icon' => 'fa-triangle-exclamation', 'color' => 'var(--warn)'],
                    'error_resolved' => ['icon' => 'fa-circle-check',          'color' => 'var(--ok)'],
                    'new_chapter'    => ['icon' => 'fa-book-open',             'color' => 'var(--ok)'],
                    'new_message'    => ['icon' => 'fa-envelope',              'color' => 'var(--accent)'],
                    'beta_chapter'   => ['icon' => 'fa-users',                 'color' => 'var(--tag-mystery-fg)'],
                    'new_comment'    => ['icon' => 'fa-comments',              'color' => 'var(--accent)'],
                ];
                $icon = $iconMap[$type] ?? ['icon' => 'fa-bell', 'color' => 'var(--text-muted)'];
                $notifUrl = null;
                if ($type === 'chapter_error' && isset($data['novel_id'], $data['chapter_id'])) {
                    $notifUrl = route('novel.read', [$data['novel_id'], $data['chapter_id']]);
                } elseif ($type === 'new_comment' && !empty($data['target_url'])) {
                    $notifUrl = $data['target_url'];
                } elseif (!empty($data['link'])) {
                    $notifUrl = $data['link'];
                }
            @endphp

            <div class="eri-card" style="display:flex;align-items:flex-start;gap:14px;padding:16px 18px;{{ $isRead ? '' : 'border-color:var(--accent);background:var(--accent-soft);' }}">
                <span style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--surface-2);color:{{ $icon['color'] }};display:grid;place-items:center;flex-shrink:0;">
                    <i class="fa-solid {{ $icon['icon'] }}"></i>
                </span>
                <div style="flex:1;min-width:0;">
                    @if($notifUrl)
                        <a href="{{ $notifUrl }}" wire:click.prevent="markAsReadAndRedirect('{{ $notification->id }}', '{{ $notifUrl }}')"
                           style="font-size:14px;color:var(--text);font-weight:500;text-decoration:none;line-height:1.4;display:block;">
                            {{ $data['message'] ?? 'Новое уведомление' }}
                        </a>
                    @else
                        <p style="font-size:14px;color:var(--text);font-weight:500;line-height:1.4;margin:0;">{{ $data['message'] ?? 'Новое уведомление' }}</p>
                    @endif
                    @if($type === 'chapter_error' && !empty($data['selected_text']))
                        <p style="font-size:12px;color:var(--text-3);margin:4px 0 0;font-style:italic;font-family:var(--serif);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">«{{ $data['selected_text'] }}»</p>
                    @endif
                    @if($type === 'chapter_error' && !empty($data['suggestion']))
                        <p style="font-size:12px;color:var(--ok);margin:4px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Предложение: {{ $data['suggestion'] }}</p>
                    @endif

                    @if($type === 'new_comment' && !empty($data['content']))
                        <div class="notif-comment-body">
                            {!! \App\Services\CommentContentRenderer::toSafeHtml($data['content']) ?: e($data['content']) !!}
                        </div>
                        @if(!empty($data['target_title']))
                            <div class="notif-comment-target">
                                <i class="fa-solid fa-arrow-turn-up" style="transform:rotate(90deg);font-size:10px;"></i>
                                <span>{{ $data['target_title'] }}</span>
                            </div>
                        @endif
                    @endif

                    <span style="font-size:11px;color:var(--text-muted);font-family:var(--mono);margin-top:6px;display:block;">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                @if(!$isRead)
                    <button wire:click="markAsRead('{{ $notification->id }}')" title="Прочитано"
                            style="background:none;border:0;color:var(--text-muted);cursor:pointer;padding:6px;border-radius:var(--r-sm);flex-shrink:0;">
                        <i class="fa-solid fa-check"></i>
                    </button>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:64px 20px;color:var(--text-muted);">
                <i class="fa-regular fa-bell" style="font-size:42px;opacity:0.4;display:block;margin-bottom:12px;"></i>
                <p style="font-weight:500;margin:0 0 4px;color:var(--text-3);">Нет уведомлений</p>
                <p style="font-size:13px;margin:0;">В этом разделе пока пусто</p>
            </div>
        @endforelse
    </div>

    <div style="margin-top:24px;">{{ $notifications->links() }}</div>
</div>
