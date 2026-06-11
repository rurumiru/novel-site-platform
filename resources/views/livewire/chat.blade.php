<div class="eri-section" style="padding-top:18px;padding-bottom:18px;">
    <div style="display:flex;height:calc(100vh - 140px);min-height:520px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r-md);overflow:hidden;">
        
        <div style="width:100%;max-width:360px;border-right:1px solid var(--border);display:{{ $selectedUser ? 'none' : 'flex' }};flex-direction:column;" class="chat-sidebar">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border);background:var(--surface-2);">
                <h2 style="font-family:var(--display);font-size:18px;font-weight:500;letter-spacing:-0.01em;color:var(--text);margin:0 0 12px;display:inline-flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-comments" style="color:var(--accent);font-size:15px;"></i> Сообщения
                </h2>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Поиск или начать диалог..." class="eri-input">
            </div>
            <div style="flex:1;overflow-y:auto;">
                @forelse($users as $user)
                    @php $unread = \App\Models\Message::where('sender_id', $user->id)->where('receiver_id', Auth::id())->where('is_read', false)->count(); @endphp
                    <button wire:click="selectUser({{ $user->id }})"
                            style="width:100%;display:flex;align-items:center;gap:12px;padding:14px 18px;background:{{ $selectedUser?->id === $user->id ? 'var(--accent-soft)' : 'transparent' }};border:0;border-bottom:1px solid var(--border);border-left:3px solid {{ $selectedUser?->id === $user->id ? 'var(--accent)' : 'transparent' }};cursor:pointer;font-family:inherit;text-align:left;transition:background 0.15s;">
                        <div style="position:relative;flex-shrink:0;">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                            @if($unread > 0)
                                <span style="position:absolute;top:-2px;right:-2px;min-width:18px;height:18px;padding:0 5px;display:inline-flex;align-items:center;justify-content:center;background:var(--err);color:#fff;font-size:10px;font-weight:700;border-radius:var(--r-pill);">{{ $unread > 99 ? '99+' : $unread }}</span>
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;color:var(--text);font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->name }}</div>
                            <div style="font-size:12px;color:var(--text-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                @if($user->novels()->exists()) Автор @else Пользователь @endif
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:var(--text-faint);font-size:11px;flex-shrink:0;"></i>
                    </button>
                @empty
                    <div style="padding:36px;text-align:center;color:var(--text-muted);">
                        <i class="fa-regular fa-comments" style="font-size:36px;opacity:0.5;display:block;margin-bottom:12px;"></i>
                        <p style="font-size:13px;margin:0 0 4px;">Нет диалогов</p>
                        <p style="font-size:11px;color:var(--text-3);margin:0;">Нажмите «Связаться» в карточке пользователя</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div style="flex:1;display:{{ !$selectedUser ? 'none' : 'flex' }};flex-direction:column;" class="chat-main">
            @if($selectedUser)
                <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;background:var(--surface);">
                    <button wire:click="$set('selectedUser', null)" class="eri-btn ghost sm" style="margin-right:10px;padding:6px 10px;">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <a href="{{ route('users.show', $selectedUser->id) }}" wire:navigate style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;text-decoration:none;color:inherit;">
                        <img src="{{ $selectedUser->avatar_url }}" alt="{{ $selectedUser->name }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                        <div style="min-width:0;">
                            <span style="font-weight:600;color:var(--text);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selectedUser->name }}</span>
                            <span style="font-size:12px;color:var(--text-3);">{{ $selectedUser->novels()->exists() ? 'Автор' : 'Пользователь' }}</span>
                        </div>
                    </a>
                </div>

                <div id="chat-messages" style="flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:14px;background:var(--surface-2);">
                    @foreach($messages as $msg)
                        @php $isOwn = $msg->sender_id === Auth::id(); @endphp
                        <div style="display:flex;justify-content:{{ $isOwn ? 'flex-end' : 'flex-start' }};">
                            <div style="display:flex;flex-direction:column;align-items:{{ $isOwn ? 'flex-end' : 'flex-start' }};max-width:75%;">
                                <div style="padding:10px 14px;border-radius:var(--r-md);font-size:14px;line-height:1.4;{{ $isOwn ? 'background:var(--accent);color:var(--accent-text);border-bottom-right-radius:4px;' : 'background:var(--surface);color:var(--text);border:1px solid var(--border);border-bottom-left-radius:4px;' }}">
                                    {{ $msg->message }}
                                </div>
                                @if(!$isOwn && !$msg->is_spam)
                                    <button wire:click="reportSpam({{ $msg->id }})" wire:confirm="Отправить жалобу на спам?"
                                            style="margin-top:4px;background:none;border:0;color:var(--text-muted);font-size:10px;cursor:pointer;font-family:inherit;">
                                        <i class="fa-solid fa-flag"></i> Пожаловаться
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="padding:14px 18px;background:var(--surface);border-top:1px solid var(--border);">
                    <form wire:submit.prevent="sendMessage" style="display:flex;gap:8px;">
                        <input type="text" wire:model="messageText" placeholder="Напишите сообщение..." class="eri-input" style="flex:1;">
                        <button type="submit" class="eri-btn primary" style="padding:10px 16px;">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            @else
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);padding:32px;text-align:center;">
                    <i class="fa-regular fa-comments" style="font-size:56px;opacity:0.5;margin-bottom:16px;"></i>
                    <p style="font-weight:500;color:var(--text-3);margin:0 0 6px;">Выберите собеседника</p>
                    <p style="font-size:13px;max-width:320px;margin:0;">Нажмите «Связаться» в профиле пользователя или в разделе «Пользователи»</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        @media (min-width: 768px) {
            .chat-sidebar { display: flex !important; }
            .chat-main { display: flex !important; }
        }
    </style>
</div>
