@php $types = \App\Livewire\HomepageEditor::moduleTypes(); @endphp
<div x-data x-on:toggle-homepage-editor.window="$wire.toggleEdit()">
    @if($editing)
    <div style="position:fixed; inset:0; z-index:60; display:flex; justify-content:flex-end;" x-data x-transition>
        <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(6px);" wire:click="toggleEdit"></div>

        <div class="eri-card" style="position:relative; width:100%; max-width:440px; height:100%; overflow-y:auto; border-radius:0; border-top:none; border-bottom:none; border-right:none; display:flex; flex-direction:column; padding:0;">
            <div style="position:sticky; top:0; background:var(--surface); border-bottom:1px solid var(--border); padding:16px 20px; display:flex; align-items:center; justify-content:space-between; z-index:10;">
                <div>
                    <h2 style="font-family:var(--display); font-size:18px; font-weight:500; color:var(--text); margin:0;">Настройка страницы</h2>
                    <p style="font-size:12px; color:var(--text-muted); margin:2px 0 0;">Перемещайте, скрывайте и добавляйте блоки</p>
                </div>
                <button wire:click="toggleEdit" type="button" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:none; border:none; color:var(--text-muted); cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="flex:1; padding:14px 16px; display:flex; flex-direction:column; gap:8px;">
                @foreach($modules as $i => $mod)
                    @php
                        $meta = $types[$mod['type']] ?? ['icon' => 'fa-cube', 'label' => 'Модуль', 'color' => 'slate'];
                        $isVisible = $mod['visible'] ?? true;
                        $isPinned = $mod['pinned'] ?? false;
                    @endphp
                    <div class="eri-card" style="padding:0; {{ $isVisible ? '' : 'opacity:0.55;' }}">
                        <div style="display:flex; align-items:center; gap:12px; padding:10px 12px;">
                            <span style="width:32px; height:32px; border-radius:8px; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fa-solid {{ $meta['icon'] }}" style="font-size:12px;"></i>
                            </span>

                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:600; font-size:13px; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $mod['title'] }}</div>
                                <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase;">{{ $meta['label'] }}</div>
                            </div>

                            <div style="display:flex; align-items:center; gap:2px; flex-shrink:0;">
                                <button wire:click="moveUp({{ $i }})" type="button" class="eri-btn sm" style="padding:6px 8px; {{ $i === 0 ? 'opacity:0.3; pointer-events:none;' : '' }}">
                                    <i class="fa-solid fa-chevron-up" style="font-size:10px;"></i>
                                </button>
                                <button wire:click="moveDown({{ $i }})" type="button" class="eri-btn sm" style="padding:6px 8px; {{ $i === count($modules) - 1 ? 'opacity:0.3; pointer-events:none;' : '' }}">
                                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                                </button>
                                @if(!$isPinned)
                                <button wire:click="toggleVisibility({{ $i }})" type="button" class="eri-btn sm" style="padding:6px 8px; color:{{ $isVisible ? 'var(--ok)' : 'var(--text-muted)' }};">
                                    <i class="fa-solid {{ $isVisible ? 'fa-eye' : 'fa-eye-slash' }}" style="font-size:10px;"></i>
                                </button>
                                <button wire:click="removeModule({{ $i }})" type="button" class="eri-btn sm" style="padding:6px 8px; color:var(--err);">
                                    <i class="fa-solid fa-trash" style="font-size:10px;"></i>
                                </button>
                                @else
                                <span style="padding:6px 8px; color:var(--text-muted);" title="Обязательный блок"><i class="fa-solid fa-lock" style="font-size:10px;"></i></span>
                                @endif
                            </div>
                        </div>

                        @if($isVisible && in_array($mod['type'], ['collection', 'top_rated', 'random', 'favorites']))
                        <div style="padding:0 12px 10px; display:flex; gap:4px;">
                            @foreach(['swiper' => 'Свайпер', 'grid' => 'Сетка', 'list' => 'Список'] as $lKey => $lLabel)
                                <button wire:click="changeLayout({{ $i }}, '{{ $lKey }}')" type="button"
                                    class="eri-btn sm {{ ($mod['layout'] ?? 'swiper') === $lKey ? 'primary' : '' }}"
                                    style="flex:1; padding:5px 8px; font-size:10px;">
                                    {{ $lLabel }}
                                </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                @endforeach

                @if(!$showAddPanel)
                <button wire:click="$set('showAddPanel', true)" type="button" class="eri-btn" style="width:100%; padding:12px; border-style:dashed;">
                    <i class="fa-solid fa-plus"></i> Добавить блок
                </button>
                @else
                <div class="eri-card" style="padding:12px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:12px; font-weight:600; color:var(--text);">Выберите тип</span>
                        <button wire:click="$set('showAddPanel', false)" type="button" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i class="fa-solid fa-xmark" style="font-size:11px;"></i></button>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        @foreach($types as $tKey => $tMeta)
                            <button wire:click="addModule('{{ $tKey }}')" type="button" class="eri-btn" style="display:flex; align-items:center; gap:12px; padding:8px 12px; justify-content:flex-start; text-align:left;">
                                <span style="width:26px; height:26px; border-radius:8px; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="fa-solid {{ $tMeta['icon'] }}" style="font-size:10px;"></i>
                                </span>
                                <span style="font-size:13px; font-weight:500;">{{ $tMeta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div style="position:sticky; bottom:0; background:var(--surface); border-top:1px solid var(--border); padding:14px 20px; display:flex; gap:8px;">
                <x-eriiba.btn wire:click="resetToDefault" block icon="rotate-left">По умолчанию</x-eriiba.btn>
                <x-eriiba.btn wire:click="saveLayout" variant="primary" block icon="check">Сохранить</x-eriiba.btn>
            </div>
        </div>
    </div>
    @endif
</div>
