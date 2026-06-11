<div style="display:flex; flex-direction:column; gap:14px;">
    @if($feedback)
        <div class="eri-alert {{ $feedbackType === 'success' ? 'ok' : '' }}">{{ $feedback }}</div>
    @endif

    <div x-data="{ pickRole: 'editor' }">
        <div class="eri-label" style="margin-bottom:6px;">Назначить роль при добавлении</div>
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
            @foreach($roleLabels as $key => $label)
                <button type="button" @click="pickRole = '{{ $key }}'"
                        x-bind:class="pickRole === '{{ $key }}' ? 'eri-btn primary sm' : 'eri-btn sm'">{{ $label }}</button>
            @endforeach
        </div>

        <div style="position:relative;">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Поиск пользователя по имени или email…"
                   class="eri-input">

            @if(count($searchResults))
            <div class="eri-card" style="position:absolute; top:100%; left:0; right:0; margin-top:4px; padding:0; box-shadow:var(--shadow-md); z-index:20; overflow:hidden;">
                @foreach($searchResults as $u)
                <button type="button" @click="$wire.addEditor({{ $u['id'] }}, pickRole)"
                        style="width:100%; display:flex; align-items:center; gap:10px; padding:10px 12px; background:none; border:none; cursor:pointer; text-align:left; color:var(--text);"
                        onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
                    <span style="width:32px; height:32px; border-radius:50%; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">{{ mb_strtoupper(mb_substr($u['name'], 0, 1)) }}</span>
                    <div style="min-width:0; flex:1;">
                        <div style="font-size:13px; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $u['name'] }}</div>
                        <div style="font-size:11px; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $u['email'] }}</div>
                    </div>
                    <span x-text="pickRole" style="font-size:10px; color:var(--accent); font-weight:700; flex-shrink:0; padding:2px 8px; background:var(--accent-soft); border-radius:var(--r-pill);"></span>
                    <i class="fa-solid fa-plus" style="color:var(--accent); flex-shrink:0;"></i>
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if($editors->isEmpty())
        <p style="font-size:13px; color:var(--text-muted); text-align:center; padding:14px 0; margin:0; font-family:var(--serif); font-style:italic;">Команда пока пустая</p>
    @else
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($editors as $editor)
                @php $pd = $editor->pivot_data; @endphp
                <div class="eri-card" x-data="{ expanded: false }" style="padding:0; overflow:hidden;">
                    
                    <div style="display:flex; align-items:center; gap:10px; padding:10px 14px;">
                        <span style="width:32px; height:32px; border-radius:50%; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">{{ mb_strtoupper(mb_substr($editor->name, 0, 1)) }}</span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $editor->name }}</div>
                            <div style="font-size:10px; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $editor->email }} · <span style="color:var(--accent); font-weight:600;">{{ $roleLabels[$pd['role_label'] ?? 'editor'] ?? 'Редактор' }}</span>
                            </div>
                        </div>
                        <button type="button" @click="expanded = !expanded" class="eri-btn ghost sm" title="Настроить права">
                            <i class="fa-solid" x-bind:class="expanded ? 'fa-chevron-up' : 'fa-cog'"></i>
                        </button>
                        <button type="button" wire:click="removeEditor({{ $editor->id }})"
                                wire:confirm="Удалить {{ $editor->name }} из команды?"
                                class="eri-btn ghost sm" style="color:var(--err);" title="Удалить">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div x-show="expanded" x-collapse style="padding:10px 14px; background:var(--surface-2); border-top:1px solid var(--border);">
                        
                        <div class="eri-label" style="margin-bottom:6px;">Роль / пресет прав</div>
                        <div style="display:flex; gap:4px; flex-wrap:wrap; margin-bottom:14px;">
                            @foreach($roleLabels as $key => $label)
                                <button type="button"
                                        wire:click="changeRole({{ $editor->id }}, '{{ $key }}')"
                                        class="eri-btn sm {{ ($pd['role_label'] ?? 'editor') === $key ? 'primary' : '' }}"
                                        style="font-size:11px; padding:5px 10px;">{{ $label }}</button>
                            @endforeach
                        </div>

                        <div class="eri-label" style="margin-bottom:6px;">Точные права</div>
                        <div style="display:grid; gap:6px;">
                            @foreach($flags as $flagKey => [$flagLabel, $flagHint])
                                <label style="display:flex; align-items:flex-start; gap:8px; padding:6px 10px; background:var(--surface); border:1px solid var(--border); border-radius:var(--r-sm); cursor:pointer;">
                                    <input type="checkbox"
                                           wire:click="toggleFlag({{ $editor->id }}, '{{ $flagKey }}')"
                                           {{ !empty($pd[$flagKey]) ? 'checked' : '' }}
                                           class="eri-checkbox" style="margin-top:2px;">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:12px; font-weight:600; color:var(--text);">{{ $flagLabel }}</div>
                                        <div style="font-size:10px; color:var(--text-3); line-height:1.4;">{{ $flagHint }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
