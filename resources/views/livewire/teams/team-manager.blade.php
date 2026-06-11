@php
    $roleOptions = [
        'coordinator'  => 'Координатор',
        'translator'   => 'Переводчик',
        'editor'       => 'Редактор',
        'typesetter'   => 'Вёрстка',
        'illustrator'  => 'Художник',
        'tlc'          => 'TL-check',
        'beta'         => 'Бета-ридер',
        'member'       => 'Участник',
    ];
    $isLeader = $team->isLeader(Auth::id());
@endphp

<div>
    
    @if(session('success'))
        <div class="eri-alert ok" style="margin-bottom:14px;"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="eri-alert err" style="margin-bottom:14px;"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <div class="tm-hero">
        @if($team->banner_path)
            <div class="tm-hero-banner" style="background-image: url('{{ \App\Models\Novel::storageUrl($team->banner_path) }}');"></div>
        @endif
        <div class="tm-hero-body">
            @if($team->logo_path)
                <img src="{{ \App\Models\Novel::storageUrl($team->logo_path) }}" class="tm-hero-logo" alt="">
            @else
                <div class="tm-hero-logo tm-hero-logo-placeholder">{{ mb_strtoupper(mb_substr($team->name, 0, 2)) }}</div>
            @endif
            <div class="tm-hero-info">
                <div class="tm-hero-name">{{ $team->name }}</div>
                @if($team->mission)
                    <p class="tm-hero-mission">{{ $team->mission }}</p>
                @endif
                <div class="tm-hero-meta">
                    <span class="tm-status-pill" style="--sc: {{ $team->status === 'recruiting' ? 'var(--ok)' : 'var(--text-muted)' }};">
                        ● {{ $team->status_label }}
                    </span>
                    <span>{{ $members->where('is_active', true)->count() }} участников</span>
                    <span>{{ $novels->count() }} новелл</span>
                    <span style="color:var(--gold);">{{ number_format($team->total_earned, 0, '.', ' ') }} ₽ заработано</span>
                    @if($team->is_official)
                        <span class="tm-badge official"><i class="fa-solid fa-check"></i> Официальная</span>
                    @endif
                </div>
            </div>
            @if($isLeader)
                <button @click="$wire.showTeamSettingsForm = true" class="eri-btn sm">
                    <i class="fa-solid fa-cog"></i> Настройки
                </button>
            @endif
        </div>
    </div>

    <div class="eri-tabs" style="margin: 18px 0;">
        <button type="button" :class="$wire.activeTab === 'members' ? 'eri-tab on' : 'eri-tab'"
                @click="$wire.activeTab = 'members'">
            <i class="fa-solid fa-users"></i> Участники
            <span style="font-size:10px; background:var(--accent); color:#fff; border-radius:99px; padding:1px 7px; margin-left:4px;">
                {{ $members->where('is_active', true)->count() }}
            </span>
        </button>
        <button type="button" :class="$wire.activeTab === 'novels' ? 'eri-tab on' : 'eri-tab'"
                @click="$wire.activeTab = 'novels'">
            <i class="fa-solid fa-book"></i> Новеллы
        </button>
        @if($pendingApplications->count() > 0)
        <button type="button" :class="$wire.activeTab === 'applications' ? 'eri-tab on' : 'eri-tab'"
                @click="$wire.activeTab = 'applications'">
            <i class="fa-solid fa-inbox"></i> Заявки
            <span style="font-size:10px; background:var(--warn); color:#fff; border-radius:99px; padding:1px 7px; margin-left:4px;">
                {{ $pendingApplications->count() }}
            </span>
        </button>
        @endif
        <button type="button" :class="$wire.activeTab === 'log' ? 'eri-tab on' : 'eri-tab'"
                @click="$wire.activeTab = 'log'">
            <i class="fa-solid fa-clock-rotate-left"></i> История
        </button>
        @if($isLeader)
        <button type="button" :class="$wire.activeTab === 'invite' ? 'eri-tab on' : 'eri-tab'"
                @click="$wire.activeTab = 'invite'">
            <i class="fa-solid fa-user-plus"></i> Пригласить
        </button>
        @endif
    </div>

    <div x-show="$wire.activeTab === 'members'" x-cloak>

        <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px; padding:12px 16px; background:var(--surface-2); border-radius:var(--r-md); flex-wrap:wrap;">
            <div>
                <span style="font-size:11px; color:var(--text-muted);">Доля лидера</span>
                <div style="font-family:var(--display); font-size:22px; font-weight:500; color:var(--gold);">{{ number_format($leaderShare, 1) }}%</div>
            </div>
            <div style="height:36px; width:1px; background:var(--border);"></div>
            <div>
                <span style="font-size:11px; color:var(--text-muted);">Распределено участникам</span>
                <div style="font-family:var(--display); font-size:22px; font-weight:500; color:{{ $totalShare > 100 ? 'var(--err)' : 'var(--accent)' }};">
                    {{ number_format($totalShare, 1) }}%
                </div>
            </div>
            @if($totalShare > 100)
                <span style="color:var(--err); font-size:12px; font-weight:600;"><i class="fa-solid fa-triangle-exclamation"></i> Сумма долей превышает 100%!</span>
            @endif
        </div>

        <div class="editor-section" style="padding:0; overflow:hidden;">
            
            @php $leader = $members->firstWhere('user_id', $team->leader_id); @endphp
            @if($leader && $leader->is_active)
                <div class="tm-member-row tm-leader">
                    <div class="tm-member-avatar">
                        @if($leader->user?->avatar_url)
                            <img src="{{ $leader->user->avatar_url }}" alt="">
                        @else
                            {{ mb_strtoupper(mb_substr($leader->user?->name ?? 'L', 0, 2)) }}
                        @endif
                        <i class="fa-solid fa-crown tm-crown"></i>
                    </div>
                    <div class="tm-member-info">
                        <div class="tm-member-name">{{ $leader->user?->name ?? '—' }}</div>
                        <div class="tm-member-role" style="color:var(--gold);">{{ $leader->display_title }}</div>
                    </div>
                    <div class="tm-member-share">
                        <span>{{ number_format($leaderShare, 1) }}%</span>
                        <small>доля</small>
                    </div>
                    <div class="tm-member-actions">
                        @if($isLeader && $leader->user_id === Auth::id())
                            <button @click="$wire.openMemberEdit({{ $leader->id }})" class="eri-btn sm">
                                <i class="fa-solid fa-pen" style="font-size:10px;"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @foreach($members->where('user_id', '!=', $team->leader_id)->where('is_active', true) as $m)
                <div class="tm-member-row">
                    <div class="tm-member-avatar">
                        @if($m->user?->avatar_url)
                            <img src="{{ $m->user->avatar_url }}" alt="">
                        @else
                            {{ mb_strtoupper(mb_substr($m->user?->name ?? '?', 0, 2)) }}
                        @endif
                    </div>
                    <div class="tm-member-info">
                        <div class="tm-member-name">
                            {{ $m->user?->name ?? '—' }}
                            @if($m->user?->username)
                                <span style="color:var(--text-muted); font-size:11px;">{{ '@' . $m->user->username }}</span>
                            @endif
                        </div>
                        <div class="tm-member-role" style="color:{{ $m->role_color }};">
                            {{ $m->display_title }}
                        </div>
                        
                        <div style="display:flex; gap:4px; margin-top:4px;">
                            @if($m->can_publish)
                                <span title="Может публиковать" class="tm-priv-badge"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                            @endif
                            @if($m->can_invite_members)
                                <span title="Может приглашать" class="tm-priv-badge"><i class="fa-solid fa-user-plus"></i></span>
                            @endif
                            @if($m->can_assign_novels)
                                <span title="Может назначать новеллы" class="tm-priv-badge"><i class="fa-solid fa-book"></i></span>
                            @endif
                            @if($m->can_manage_payouts)
                                <span title="Управление выплатами" class="tm-priv-badge"><i class="fa-solid fa-coins"></i></span>
                            @endif
                            @if($m->can_edit_any_chapter)
                                <span title="Может редактировать любые главы" class="tm-priv-badge"><i class="fa-solid fa-pen-to-square"></i></span>
                            @endif
                        </div>
                    </div>
                    <div class="tm-member-share">
                        <span>{{ number_format($m->revenue_share, 1) }}%</span>
                        <small>доля</small>
                    </div>
                    <div class="tm-member-actions">
                        <button @click="$wire.openMemberEdit({{ $m->id }})" class="eri-btn sm" title="Редактировать">
                            <i class="fa-solid fa-pen" style="font-size:10px;"></i>
                        </button>
                        @if($novels->count() > 0)
                            <div x-data="{ open: false }" style="position:relative;">
                                <button @click="open = !open" class="eri-btn sm" title="Доля на новелле">
                                    <i class="fa-solid fa-percent" style="font-size:10px;"></i>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     style="position:absolute; right:0; top:100%; margin-top:4px; background:var(--surface); border:1px solid var(--border); border-radius:var(--r-md); box-shadow:var(--shadow-md); padding:8px; z-index:50; min-width:200px;">
                                    <div style="font-size:11px; font-weight:600; color:var(--text-muted); padding:4px 8px 8px; border-bottom:1px solid var(--border); margin-bottom:6px;">
                                        Персональная доля на новелле
                                    </div>
                                    @foreach($novels as $nov)
                                        <button @click="$wire.openNovelShare({{ $m->id }}, {{ $nov->id }}); open = false"
                                                style="display:flex; width:100%; gap:8px; align-items:center; padding:6px 8px; border-radius:var(--r-sm); background:none; border:0; cursor:pointer; text-align:left; font-size:12px; color:var(--text);">
                                            <i class="fa-solid fa-book" style="color:var(--accent); flex-shrink:0; font-size:10px;"></i>
                                            <span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $nov->title }}</span>
                                            @php
                                                $sh = $novelShares->firstWhere(fn($s) => $s->novel_id == $nov->id && $s->user_id == $m->user_id);
                                            @endphp
                                            @if($sh)
                                                <span style="color:var(--accent); font-weight:600; flex-shrink:0;">{{ $sh->revenue_share }}%</span>
                                            @else
                                                <span style="color:var(--text-muted); flex-shrink:0;">обычная</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($isLeader || Auth::user()->can('teams.kick_members'))
                            <button @click="$wire.openKick({{ $m->id }})" class="eri-btn sm" style="color:var(--err);" title="Исключить">
                                <i class="fa-solid fa-user-xmark" style="font-size:10px;"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @php $formerMembers = $members->where('is_active', false); @endphp
        @if($formerMembers->count() > 0)
            <details style="margin-top:14px; color:var(--text-muted);">
                <summary style="cursor:pointer; font-size:12px; padding:8px 0;">Бывшие участники ({{ $formerMembers->count() }})</summary>
                @foreach($formerMembers as $m)
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 12px; opacity:0.6; border-bottom:1px solid var(--border);">
                        <div class="eri-avatar" style="width:32px; height:32px; font-size:11px;">{{ mb_strtoupper(mb_substr($m->user?->name ?? '?', 0, 2)) }}</div>
                        <span style="font-size:13px;">{{ $m->user?->name ?? '—' }}</span>
                        <span style="font-size:11px; color:var(--text-muted);">{{ $m->role_label }}</span>
                        <span style="font-size:11px; margin-left:auto;">
                            {{ $m->left_at ? $m->left_at->format('d.m.Y') : '—' }}
                        </span>
                    </div>
                @endforeach
            </details>
        @endif
    </div>

    <div x-show="$wire.activeTab === 'novels'" x-cloak>
        @forelse($novels as $nov)
            @php $pivot = $nov->pivot; @endphp
            <div style="display:grid; grid-template-columns:auto 1fr auto; gap:14px; align-items:center; padding:14px; border-bottom:1px solid var(--border);">
                <x-eriiba.cover :novel="$nov" style="width:48px; height:64px;" />
                <div>
                    <div style="font-weight:600; color:var(--text); margin-bottom:4px;">{{ $nov->title }}</div>
                    <div style="font-size:12px; color:var(--text-muted);">
                        <span>Доля команды: <strong style="color:var(--accent);">{{ $pivot->team_revenue_share ?? 0 }}%</strong></span>
                        @if($pivot->show_credits)<span style="margin-left:10px;"><i class="fa-solid fa-eye"></i> Кредиты видны</span>@endif
                    </div>
                </div>
                <div style="font-size:11px; color:var(--text-muted); text-align:right;">
                    @php
                        $shares = $novelShares->where('novel_id', $nov->id);
                    @endphp
                    @if($shares->count() > 0)
                        <div style="margin-top:4px; font-size:11px; color:var(--text-muted);">
                            Персональные доли: {{ $shares->count() }} уч.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding:32px; text-align:center; color:var(--text-muted);">Команда ещё не привязана ни к одной новелле.</div>
        @endforelse
    </div>

    <div x-show="$wire.activeTab === 'applications'" x-cloak>
        @forelse($pendingApplications as $app)
            <div style="display:grid; grid-template-columns:auto 1fr auto; gap:14px; align-items:center; padding:14px; border-bottom:1px solid var(--border);">
                <div class="eri-avatar" style="width:40px; height:40px; font-size:13px;">
                    @if($app->user?->avatar_url)
                        <img src="{{ $app->user->avatar_url }}" alt="">
                    @else
                        {{ mb_strtoupper(mb_substr($app->user?->name ?? '?', 0, 2)) }}
                    @endif
                </div>
                <div>
                    <div style="font-weight:600;">{{ $app->user?->name }}</div>
                    <div style="font-size:11px; color:var(--text-muted);">Желаемая роль: {{ $app->desired_role }}</div>
                    @if($app->cover_letter)
                        <p style="font-size:12px; color:var(--text-2); margin:6px 0 0; font-style:italic;">«{{ Str::limit($app->cover_letter, 200) }}»</p>
                    @endif
                    @if($app->portfolio_url)
                        <a href="{{ $app->portfolio_url }}" target="_blank" style="font-size:11px; color:var(--accent);">Портфолио →</a>
                    @endif
                </div>
                <div style="display:flex; gap:6px;">
                    <button wire:click="acceptApplication({{ $app->id }})" class="eri-btn sm primary">
                        <i class="fa-solid fa-check"></i> Принять
                    </button>
                    <button wire:click="rejectApplication({{ $app->id }})" class="eri-btn sm" style="color:var(--err);">
                        <i class="fa-solid fa-xmark"></i> Отклонить
                    </button>
                </div>
            </div>
        @empty
            <div style="padding:32px; text-align:center; color:var(--text-muted);">Новых заявок нет.</div>
        @endforelse
    </div>

    <div x-show="$wire.activeTab === 'log'" x-cloak>
        @forelse($kickLogs as $log)
            <div style="display:grid; grid-template-columns:auto 1fr auto; gap:12px; align-items:start; padding:12px 14px; border-bottom:1px solid var(--border);">
                <div style="width:8px; height:8px; border-radius:50%; margin-top:5px; flex-shrink:0; background:{{ match($log->action) { 'kick','ban' => 'var(--err)', 'leave' => 'var(--warn)', 'role_change','share_change' => 'var(--accent)', default => 'var(--border)' } }}"></div>
                <div>
                    <div style="font-size:13px; font-weight:500; color:var(--text);">
                        {{ $log->action_label }} — <strong>{{ $log->user?->name ?? '—' }}</strong>
                        @if($log->kicked_by !== $log->user_id)
                            <span style="color:var(--text-muted); font-size:11px;"> от {{ $log->kickedBy?->name }}</span>
                        @endif
                    </div>
                    @if($log->reason)
                        <div style="font-size:12px; color:var(--text-2); margin-top:2px; font-style:italic;">«{{ $log->reason }}»</div>
                    @endif
                    @if($log->prev_role && $log->new_role)
                        <div style="font-size:11px; color:var(--text-muted);">{{ $log->prev_role }} → {{ $log->new_role }}</div>
                    @endif
                    @if($log->prev_share !== null && $log->new_share !== null)
                        <div style="font-size:11px; color:var(--text-muted);">{{ $log->prev_share }}% → {{ $log->new_share }}%</div>
                    @endif
                </div>
                <div style="font-size:11px; color:var(--text-muted);">{{ $log->logged_at?->format('d.m.Y H:i') }}</div>
            </div>
        @empty
            <div style="padding:32px; text-align:center; color:var(--text-muted);">История пуста.</div>
        @endforelse
    </div>

    @if($isLeader)
    <div x-show="$wire.activeTab === 'invite'" x-cloak>
        <div class="editor-section" style="max-width:540px;">
            <h3 style="font-family:var(--display); font-size:16px; font-weight:500; color:var(--text); margin:0 0 14px;">
                Пригласить участника
            </h3>
            <div class="ne-field">
                <label class="eri-label">Email (необязательно)</label>
                <input type="email" wire:model="inviteEmail" class="eri-input" placeholder="user@example.com">
            </div>
            <div class="editor-grid cols-2">
                <div class="ne-field">
                    <label class="eri-label">Предлагаемая роль</label>
                    <select wire:model="inviteRole" class="eri-select">
                        @foreach($roleOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ne-field">
                    <label class="eri-label">Доля от командного % (%)</label>
                    <input type="number" wire:model.number="inviteShare" min="0" max="100" step="0.5" class="eri-input">
                </div>
            </div>
            <x-eriiba.btn variant="primary" wire:click="generateInviteLink" icon="link">
                Сгенерировать ссылку
            </x-eriiba.btn>

            @if($inviteLink)
                <div class="eri-alert ok" style="margin-top:14px; word-break:break-all;">
                    <strong>Ссылка действует 7 дней:</strong><br>
                    <a href="{{ $inviteLink }}" style="color:var(--accent);">{{ $inviteLink }}</a>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if($showMemberForm)
    <div style="position:fixed; inset:0; z-index:300; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:16px;">
        <div class="eri-card" style="max-width:560px; width:100%; max-height:90vh; overflow-y:auto; padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="font-family:var(--display); font-size:20px; font-weight:500; margin:0;">Изменить участника</h2>
                <button @click="$wire.showMemberForm = false" style="background:none; border:0; color:var(--text-muted); cursor:pointer; font-size:20px;">×</button>
            </div>

            <div class="editor-grid cols-2">
                <div class="ne-field">
                    <label class="eri-label">Роль в команде</label>
                    <select wire:model="editRole" class="eri-select">
                        @foreach($roleOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('editRole') <span class="ne-err">{{ $message }}</span> @enderror
                </div>
                <div class="ne-field">
                    <label class="eri-label">Кастомный титул</label>
                    <input type="text" wire:model="editTitle" class="eri-input" placeholder="Например: Главный редактор">
                </div>
            </div>

            <div class="ne-field">
                <label class="eri-label" style="display:flex; justify-content:space-between;">
                    <span>Доля от командного % (%)</span>
                    <span style="font-family:var(--mono); color:var(--accent);">{{ $editShare }}%</span>
                </label>
                <input type="range" wire:model.number="editShare" min="0" max="100" step="0.5" style="width:100%; accent-color:var(--accent);">
                <input type="number" wire:model.number="editShare" min="0" max="100" step="0.5" class="eri-input" style="margin-top:6px; width:120px;">
            </div>

            <div class="ne-field" style="background:var(--surface-2); border-radius:var(--r-sm); padding:14px;">
                <div style="font-size:12px; font-weight:600; color:var(--text); margin-bottom:10px;">Привилегии</div>
                <div style="display:grid; gap:8px;">
                    @foreach([
                        ['editCanPublish', 'Публиковать главы без ревью'],
                        ['editCanInvite', 'Приглашать участников'],
                        ['editCanAssignNovels', 'Назначать новеллы команде'],
                        ['editCanEditAny', 'Редактировать любые главы команды'],
                        ['editCanManagePayouts', 'Управлять выплатами'],
                    ] as [$field, $label])
                    <label style="display:flex; align-items:center; justify-content:space-between; cursor:pointer; font-size:13px; color:var(--text);">
                        {{ $label }}
                        <input type="checkbox" wire:model="{{ $field }}">
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="ne-field">
                <label class="eri-label">Внутренняя заметка (только для лидера)</label>
                <textarea wire:model="editNote" rows="2" class="eri-textarea" placeholder="Например: Хорошо работает, но часто опаздывает с главами"></textarea>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
                <x-eriiba.btn @click="$wire.showMemberForm = false" block>Отмена</x-eriiba.btn>
                <x-eriiba.btn variant="primary" wire:click="saveMemberEdit" block icon="check">Сохранить</x-eriiba.btn>
            </div>
        </div>
    </div>
    @endif

    @if($showKickForm)
    <div style="position:fixed; inset:0; z-index:300; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:16px;">
        <div class="eri-card" style="max-width:480px; width:100%; padding:24px;">
            <h2 style="font-family:var(--display); font-size:20px; font-weight:500; margin:0 0 6px; color:var(--err);">
                <i class="fa-solid fa-user-xmark"></i> Исключить участника
            </h2>
            <p style="color:var(--text-muted); font-size:13px; margin:0 0 18px;">
                Участник будет исключён. Его доли на новеллах будут сохранены для истории.
            </p>

            <div class="ne-field">
                <label class="eri-label">Причина исключения <span style="color:var(--err);">*</span></label>
                <textarea wire:model="kickReason" rows="3" class="eri-textarea"
                          placeholder="Нарушение правил команды, неактивность, конфликт интересов…"></textarea>
                @error('kickReason') <span class="ne-err">{{ $message }}</span> @enderror
            </div>

            <label style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--surface-2); border-radius:var(--r-sm); cursor:pointer; margin-bottom:18px;">
                <div>
                    <div style="font-size:13px; font-weight:600; color:var(--text);">Показать в профиле участника</div>
                    <div style="font-size:11px; color:var(--text-muted);">Причина будет видна в его профиле — прозрачность для других команд</div>
                </div>
                <input type="checkbox" wire:model="kickShowInProfile">
            </label>

            <div style="display:flex; gap:8px;">
                <x-eriiba.btn @click="$wire.showKickForm = false" block>Отмена</x-eriiba.btn>
                <x-eriiba.btn variant="danger" wire:click="confirmKick" block icon="user-xmark">Исключить</x-eriiba.btn>
            </div>
        </div>
    </div>
    @endif

    @if($showNovelShareForm)
    <div style="position:fixed; inset:0; z-index:300; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:16px;">
        <div class="eri-card" style="max-width:420px; width:100%; padding:24px;">
            <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 14px;">
                Персональная доля на новелле
            </h2>
            <p style="font-size:12px; color:var(--text-muted); margin:0 0 18px;">
                Если указано — используется вместо общей доли участника для этой новеллы.
            </p>

            <div class="ne-field">
                <label class="eri-label">Доля (%)</label>
                <input type="number" wire:model.number="novelShareValue" min="0" max="100" step="0.5" class="eri-input">
            </div>

            <div class="ne-field">
                <label class="eri-label">Роль на новелле (необязательно)</label>
                <select wire:model="novelShareRole" class="eri-select">
                    <option value="">— Как в команде</option>
                    @foreach($roleOptions as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:8px; margin-top:14px;">
                <x-eriiba.btn @click="$wire.showNovelShareForm = false" block>Отмена</x-eriiba.btn>
                <x-eriiba.btn variant="primary" wire:click="saveNovelShare" block icon="check">Сохранить</x-eriiba.btn>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
/* ── Team Manager styles ── */
.tm-hero {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    overflow: hidden;
    margin-bottom: 4px;
}
.tm-hero-banner {
    height: 120px;
    background-size: cover;
    background-position: center;
    position: relative;
}
.tm-hero-body {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 20px;
    flex-wrap: wrap;
}
.tm-hero-logo {
    width: 64px; height: 64px;
    border-radius: 14px;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid var(--border);
}
.tm-hero-logo-placeholder {
    display: flex; align-items: center; justify-content: center;
    background: var(--accent-soft);
    color: var(--accent);
    font-family: var(--display);
    font-size: 22px; font-weight: 700;
}
.tm-hero-info { flex: 1; min-width: 0; }
.tm-hero-name { font-family: var(--display); font-size: 22px; font-weight: 600; letter-spacing: -0.02em; color: var(--text); }
.tm-hero-mission { font-size: 13px; color: var(--text-muted); margin: 4px 0 0; }
.tm-hero-meta { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 8px; font-size: 12px; color: var(--text-3); }
.tm-status-pill { color: var(--sc, var(--text-muted)); font-weight: 600; }
.tm-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; }
.tm-badge.official { background: var(--accent-soft); color: var(--accent); }

/* Member rows */
.tm-member-row {
    display: grid;
    grid-template-columns: 44px 1fr auto auto;
    gap: 12px;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    transition: background 0.12s;
}
.tm-member-row:hover { background: color-mix(in srgb, var(--accent) 4%, transparent); }
.tm-member-row:last-child { border-bottom: 0; }
.tm-leader { background: color-mix(in srgb, var(--gold) 6%, var(--surface)); }
.tm-member-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--accent-soft);
    color: var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--display); font-size: 14px; font-weight: 600;
    position: relative;
    flex-shrink: 0;
}
.tm-member-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tm-crown {
    position: absolute; top: -4px; right: -4px;
    font-size: 12px; color: var(--gold);
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
}
.tm-member-name { font-size: 14px; font-weight: 600; color: var(--text); }
.tm-member-role { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }
.tm-member-share { text-align: right; }
.tm-member-share span { font-family: var(--display); font-size: 18px; font-weight: 500; color: var(--text); display: block; }
.tm-member-share small { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; }
.tm-member-actions { display: flex; gap: 4px; }
.tm-priv-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px;
    background: var(--accent-soft); color: var(--accent);
    border-radius: 5px; font-size: 10px;
}
@media (max-width: 600px) {
    .tm-member-row { grid-template-columns: 36px 1fr; }
    .tm-member-share, .tm-member-actions { grid-column: 2; }
}
</style>
