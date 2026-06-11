<div class="editor-shell">
    <div class="editor-head">
        <div>
            <h1><i class="fa-solid fa-triangle-exclamation" style="color:var(--warn); margin-right:10px;"></i>Ошибки в тексте</h1>
            <p style="color:var(--text-muted); margin:4px 0 0; font-size:13px;">Ошибки, найденные читателями в ваших произведениях</p>
        </div>
        <div class="spacer"></div>
        <x-eriiba.btn :href="route('my-novels')" wire:navigate icon="arrow-left">Назад к работам</x-eriiba.btn>
    </div>

    <div style="display:flex; gap:8px; margin-bottom:18px; overflow-x:auto;">
        @foreach(['new' => 'Новые', 'reviewed' => 'Проверены', 'fixed' => 'Исправлены', 'all' => 'Все'] as $val => $label)
            <button type="button" wire:click="$set('filter', '{{ $val }}')"
                    class="eri-btn sm {{ $filter === $val ? 'primary' : '' }}" style="flex-shrink:0;">
                {{ $label }}
                @if($val !== 'all' && ($counts[$val] ?? 0) > 0)
                    <span style="margin-left:6px; padding:2px 6px; border-radius:10px; font-size:10px; background:rgba(255,255,255,0.25);">{{ $counts[$val] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="editor-grid" style="gap:10px;">
        @forelse($errors as $error)
            <div class="eri-card" style="padding:14px;">
                <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-start;">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:6px; margin-bottom:8px; font-size:12px;">
                            <strong style="color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $error->chapter?->novel?->title ?? '—' }}</strong>
                            <i class="fa-solid fa-chevron-right" style="color:var(--text-muted); font-size:8px;"></i>
                            @if($error->chapter)
                                <a href="{{ route('novel.read', [$error->chapter->novel_id, $error->chapter_id]) }}" target="_blank" style="color:var(--accent); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    {{ $error->chapter->title }}
                                    <i class="fa-solid fa-external-link-alt" style="font-size:8px; margin-left:2px;"></i>
                                </a>
                            @endif
                        </div>

                        <div class="eri-alert warn" style="margin-bottom:8px; font-style:italic;">
                            «{{ $error->selected_text }}»
                        </div>

                        @if($error->suggestion)
                            <div class="eri-alert ok" style="margin-bottom:8px;">
                                <strong style="display:block; font-size:11px; margin-bottom:2px;">Предложение:</strong>
                                {{ $error->suggestion }}
                            </div>
                        @endif

                        <div style="display:flex; align-items:center; gap:12px; font-size:11px; color:var(--text-muted);">
                            <span>{{ $error->user?->name ?? 'Аноним' }}</span>
                            <span>{{ $error->created_at->diffForHumans() }}</span>
                            <x-eriiba.chip :variant="$error->status === 'new' ? 'warn' : ($error->status === 'fixed' ? 'accent' : null)">
                                {{ $error->status === 'new' ? 'Новая' : ($error->status === 'fixed' ? 'Исправлена' : 'Проверена') }}
                            </x-eriiba.chip>
                        </div>
                    </div>

                    @if($error->status !== 'fixed')
                    <div style="display:flex; flex-direction:column; gap:6px; flex-shrink:0;">
                        @if($error->status === 'new')
                            <x-eriiba.btn size="sm" wire:click="markReviewed({{ $error->id }})" icon="eye">Проверено</x-eriiba.btn>
                        @endif
                        <x-eriiba.btn variant="primary" size="sm" wire:click="markFixed({{ $error->id }})" icon="check">Исправлено</x-eriiba.btn>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align:center; padding:60px 16px; color:var(--text-muted);">
                <i class="fa-solid fa-check-circle" style="font-size:36px; margin-bottom:12px; opacity:0.4; display:block;"></i>
                <p style="font-weight:600; margin:0 0 4px;">Ошибок нет</p>
                <p style="font-size:13px; margin:0;">Читатели пока не нашли ошибок в ваших текстах</p>
            </div>
        @endforelse
    </div>

    @if($errors->hasPages())
        <div style="margin-top:18px;">{{ $errors->links() }}</div>
    @endif
</div>
