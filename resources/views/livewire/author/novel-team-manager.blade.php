<div wire:key="novel-team-manager-{{ $novel->id }}">

    @if($teams->isEmpty())
        <div style="padding:24px;text-align:center;color:var(--text-muted);border:1.5px dashed var(--border);border-radius:var(--r-md);">
            <i class="fa-solid fa-users-slash" style="font-size:28px;margin-bottom:8px;display:block;opacity:.4;"></i>
            Команда ещё не назначена. Добавьте команду ниже.
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
            @foreach($teams as $team)
                @php
                    $pv      = $team->pivot;
                    $primary = (bool)($pv->is_primary ?? false);
                    $credits = (bool)($pv->show_credits ?? true);
                    $share   = $pv->team_revenue_share ?? 0;
                    $editing = $editingTeamId !== null && $editingTeamId === $team->id;
                @endphp
                <div style="background:var(--surface-2,var(--surface));border:1.5px solid var(--border);border-radius:var(--r-md);overflow:hidden;">

                    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border);">
                        <div style="width:36px;height:36px;border-radius:50%;background:var(--accent-dim,#e8e3ff);display:grid;place-items:center;flex-shrink:0;">
                            @if($team->logo_path)
                                <img src="{{ Storage::url($team->logo_path) }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                            @else
                                <i class="fa-solid fa-users" style="color:var(--accent);font-size:14px;"></i>
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $team->name }}
                                @if($primary)
                                    <span style="background:var(--accent);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:999px;margin-left:6px;vertical-align:middle;">ОСНОВНАЯ</span>
                                @endif
                                @if($team->is_official)
                                    <span style="background:#3b82f6;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:999px;margin-left:4px;vertical-align:middle;">✓ Офиц.</span>
                                @endif
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                Доля: <b style="color:var(--accent);">{{ $share }}%</b>
                                @if($credits) · <i class="fa-solid fa-id-card" title="Кредиты показываются"></i> кредиты
                                @else · <span style="opacity:.5;"><i class="fa-solid fa-id-card"></i> скрыты</span>
                                @endif
                                · {{ $team->activeMembers->count() }} уч.
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <button wire:click="startEdit({{ $team->id }})"
                                style="background:none;border:1px solid var(--border);border-radius:var(--r-sm);padding:5px 10px;font-size:12px;color:var(--text-2);cursor:pointer;">
                                <i class="fa-solid fa-pen-to-square" style="margin-right:4px;"></i>Изменить
                            </button>
                            <button wire:click="removeTeam({{ $team->id }})"
                                wire:confirm="Убрать команду '{{ $team->name }}' с этой новеллы?"
                                style="background:none;border:1px solid #ef4444;border-radius:var(--r-sm);padding:5px 10px;font-size:12px;color:#ef4444;cursor:pointer;">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    @if($editing)
                        <div style="padding:16px;background:var(--bg);border-top:1px solid var(--border);">
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px;">
                                <div>
                                    <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Доля команды (%)</label>
                                    <input type="number" wire:model="editShare" min="0" max="100" step="0.5"
                                        style="width:100%;background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:7px 10px;font-size:13px;color:var(--text);">
                                </div>
                                <div style="display:flex;flex-direction:column;justify-content:flex-end;padding-bottom:2px;">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                                        <input type="checkbox" wire:model="editIsPrimary" style="width:15px;height:15px;accent-color:var(--accent);">
                                        Основная команда
                                    </label>
                                </div>
                                <div style="display:flex;flex-direction:column;justify-content:flex-end;padding-bottom:2px;">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                                        <input type="checkbox" wire:model="editShowCredits" style="width:15px;height:15px;accent-color:var(--accent);">
                                        Показывать кредиты
                                    </label>
                                </div>
                            </div>
                            <div style="display:flex;gap:8px;">
                                <button wire:click="saveEdit"
                                    style="background:var(--accent);color:#fff;border:none;border-radius:var(--r-sm);padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;">
                                    Сохранить
                                </button>
                                <button wire:click="cancelEdit"
                                    style="background:none;border:1px solid var(--border);border-radius:var(--r-sm);padding:7px 14px;font-size:13px;cursor:pointer;color:var(--text-2);">
                                    Отмена
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($team->activeMembers->isNotEmpty())
                        <div style="padding:10px 16px 14px;">
                            <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px;">
                                Участники команды на этой новелле
                            </div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                @foreach($team->activeMembers as $member)
                                    @php $roleColors = ['leader'=>'#ef4444','coordinator'=>'#f59e0b','translator'=>'#10b981','editor'=>'#3b82f6','typesetter'=>'#8b5cf6','illustrator'=>'#ec4899','tlc'=>'#6366f1','beta'=>'#6b7280','member'=>'#6b7280']; @endphp
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:26px;height:26px;border-radius:50%;background:var(--surface-2,var(--surface));flex-shrink:0;overflow:hidden;display:grid;place-items:center;">
                                            @if($member->user?->avatar)
                                                <img src="{{ Storage::url($member->user->avatar) }}" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <i class="fa-solid fa-user" style="font-size:11px;color:var(--text-muted);"></i>
                                            @endif
                                        </div>
                                        <div style="flex:1;min-width:0;">
                                            <span style="font-size:13px;font-weight:500;">{{ $member->user?->name ?? '—' }}</span>
                                            <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:999px;margin-left:6px;color:#fff;background:{{ $roleColors[$member->role] ?? '#6b7280' }};">
                                                {{ $member->role_label }}
                                            </span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                            <span style="font-size:11px;color:var(--text-muted);">Доля:</span>
                                            <input type="number"
                                                value="{{ $this->getMemberShare($team->id, $member->user_id) ?: $member->revenue_share }}"
                                                min="0" max="100" step="0.5"
                                                style="width:56px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:3px 6px;font-size:12px;color:var(--text);text-align:center;"
                                                wire:change="saveMemberShare({{ $team->id }}, {{ $member->user_id }}, $event.target.value)"
                                            >
                                            <span style="font-size:11px;color:var(--text-muted);">%</span>
                                        </div>
                                        
                                        <div style="display:flex;gap:3px;">
                                            @if($member->can_publish)
                                                <span title="Может публиковать" style="font-size:10px;background:#d1fae5;color:#065f46;padding:1px 5px;border-radius:4px;">публ.</span>
                                            @endif
                                            @if($member->can_edit_any_chapter)
                                                <span title="Редактирование любых глав" style="font-size:10px;background:#dbeafe;color:#1e3a8a;padding:1px 5px;border-radius:4px;">ред.</span>
                                            @endif
                                            @if($member->can_invite_members)
                                                <span title="Может приглашать" style="font-size:10px;background:#fef3c7;color:#78350f;padding:1px 5px;border-radius:4px;">инв.</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div style="background:var(--surface);border:1.5px dashed var(--border);border-radius:var(--r-md);padding:18px;" x-data="{ open: false }">
        <button @click="open=!open"
            style="display:flex;align-items:center;gap:8px;background:none;border:none;cursor:pointer;color:var(--text);font-size:14px;font-weight:600;width:100%;text-align:left;padding:0;">
            <i class="fa-solid fa-plus" style="color:var(--accent);"></i>
            Добавить / назначить команду
            <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:11px;color:var(--text-muted);" x-bind:style="open ? 'transform:rotate(180deg)' : ''"></i>
        </button>
        <div x-show="open" x-transition style="margin-top:14px;display:none;">
            @if($availableTeams->isEmpty())
                <p style="font-size:13px;color:var(--text-muted);margin:0;">Все подходящие команды уже назначены, или нет доступных команд.</p>
            @else
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Команда</label>
                        <select wire:model="selectedTeamId"
                            style="width:100%;background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:7px 10px;font-size:13px;color:var(--text);">
                            <option value="">— Выберите команду —</option>
                            @foreach($availableTeams as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}{{ $t->is_official ? ' ✓' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Доля команды (%)</label>
                        <input type="number" wire:model="newTeamShare" min="0" max="100" step="0.5"
                            style="width:100%;background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:7px 10px;font-size:13px;color:var(--text);">
                    </div>
                </div>
                <div style="display:flex;gap:18px;margin-bottom:14px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" wire:model="isPrimary" style="width:15px;height:15px;accent-color:var(--accent);">
                        Основная команда
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" wire:model="showCredits" style="width:15px;height:15px;accent-color:var(--accent);">
                        Показывать кредиты
                    </label>
                </div>
                <button wire:click="assignTeam"
                    style="background:var(--accent);color:#fff;border:none;border-radius:var(--r-sm);padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fa-solid fa-link" style="margin-right:6px;"></i>Назначить команду
                </button>
            @endif
        </div>
    </div>

    @if(session()->has('team-saved'))
        <div style="margin-top:10px;padding:8px 14px;background:#d1fae5;color:#065f46;border-radius:var(--r-sm);font-size:13px;">
            <i class="fa-solid fa-check" style="margin-right:6px;"></i>Сохранено
        </div>
    @endif
</div>
