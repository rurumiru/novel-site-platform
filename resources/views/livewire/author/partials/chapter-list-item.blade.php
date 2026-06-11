@php
    $volumes = $volumes ?? collect();
    $groupChapters = $chapter->volume_id
        ? ($volumes->firstWhere('id', $chapter->volume_id)?->chapters ?? collect())
        : ($chapters->whereNull('volume_id') ?? collect());
    $localPos = $groupChapters->search(fn($c) => $c->id === $chapter->id);
    $localPos = $localPos === false ? 0 : $localPos + 1;
    $groupTotal = $groupChapters->count();

    $rowAccent = null;
    if ($chapter->highlight ?? null) {
        $rowAccent = in_array($chapter->highlight, ['important', 'breakthrough', 'arc-end'])
            ? '#f59e0b'
            : '#fbbf24';
    }
    $hlText = [
        'important'    => 'ВАЖНО',
        'arc-end'      => 'ФИНАЛ АРКИ',
        'arc-start'    => 'НАЧАЛО АРКИ',
        'breakthrough' => 'ПРОРЫВ',
        'special'      => 'СПЕЦ',
        'side-story'   => 'ПОБОЧНАЯ',
        'announcement' => 'АНОНС',
    ];
@endphp

<div
    wire:key="chapter-{{ $chapter->id }}"
    data-chapter-id="{{ $chapter->id }}"
    data-chapter-title="{{ $chapter->title }}"
    x-data="{ posOpen: false, newPos: {{ $localPos }} }"
    class="chapter-row"
    @if($rowAccent)
        style="border-left:3px solid {{ $rowAccent }}; background:color-mix(in srgb, {{ $rowAccent }} 6%, transparent);"
    @endif
>
    <div class="chapter-row-main"
         x-bind:style="isSelected({{ $chapter->id }}) ? 'background: var(--accent-soft);' : ''">

        <input type="checkbox"
            :checked="isSelected({{ $chapter->id }})"
            @click.stop="handleSelect({{ $chapter->id }}, $event)"
            class="chapter-row-check"
            title="Выбрать">

        <button type="button"
            class="chapter-drag-handle chapter-row-drag"
            title="Зажмите и перетащите">
            <svg width="10" height="14" viewBox="0 0 10 16" fill="currentColor"><circle cx="3" cy="3" r="1.5"/><circle cx="7" cy="3" r="1.5"/><circle cx="3" cy="8" r="1.5"/><circle cx="7" cy="8" r="1.5"/><circle cx="3" cy="13" r="1.5"/><circle cx="7" cy="13" r="1.5"/></svg>
        </button>

        <button type="button"
            @click.stop="posOpen = !posOpen; if(posOpen) $nextTick(() => $refs.posInput{{ $chapter->id }}.select())"
            class="chapter-row-pos {{ $rowAccent ? 'is-accent' : '' }}"
            title="Кликните чтобы изменить позицию ({{ $localPos }} из {{ $groupTotal }})">
            {{ $localPos }}
        </button>

        <a href="{{ route('author.chapter.edit', [$chapter->novel_id, $chapter->id]) }}" wire:navigate
           class="chapter-row-title"
           title="Редактировать главу">
            <span class="chapter-row-title-text">{{ $chapter->title }}</span>

            @if(!$chapter->is_published)
                <span class="chapter-pill pill-draft">черновик</span>
            @endif
            @if($chapter->is_locked)
                @if($chapter->price <= 0)
                    <span class="chapter-pill pill-err">0₽</span>
                @else
                    <span class="chapter-pill pill-paid">{{ $chapter->price }}₽</span>
                @endif
            @endif
            @if($chapter->published_at && $chapter->published_at->isFuture())
                <span class="chapter-pill pill-sched">{{ $chapter->published_at->format('d.m H:i') }}</span>
            @endif
            @if($chapter->highlight ?? null)
                <span class="chapter-pill pill-hl" style="background:color-mix(in srgb, {{ $rowAccent }} 18%, transparent); color:{{ $rowAccent }};">
                    {{ $hlText[$chapter->highlight] ?? mb_strtoupper($chapter->highlight) }}
                </span>
            @endif
        </a>

        <div class="chapter-row-move" role="group">
            <button type="button" wire:click="moveToTop({{ $chapter->id }})" class="cr-mbtn" title="В самое начало">
                <i class="fa-solid fa-angles-up"></i>
            </button>
            <button type="button" wire:click="moveUp({{ $chapter->id }})" class="cr-mbtn" title="Выше на одну">
                <i class="fa-solid fa-chevron-up"></i>
            </button>
            <button type="button" wire:click="moveDown({{ $chapter->id }})" class="cr-mbtn" title="Ниже на одну">
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <button type="button" wire:click="moveToBottom({{ $chapter->id }})" class="cr-mbtn" title="В самый конец">
                <i class="fa-solid fa-angles-down"></i>
            </button>
        </div>

        @if($volumes->count() > 0)
        <select wire:change="moveToVolume({{ $chapter->id }}, $event.target.value)"
            class="chapter-row-vol"
            title="Переместить в том">
            <option value="">— Без тома</option>
            @foreach($volumes as $v)
                <option value="{{ $v->id }}" {{ $chapter->volume_id == $v->id ? 'selected' : '' }}>
                    {{ Str::limit($v->title, 14) }}
                </option>
            @endforeach
        </select>
        @endif

        <div class="chapter-row-actions">
            <button type="button" wire:click="openSplit({{ $chapter->id }})" class="cr-abtn cr-warn" title="Разделить">
                <i class="fa-solid fa-scissors"></i>
            </button>
            <a href="{{ route('author.chapter.edit', [$chapter->novel_id, $chapter->id]) }}" wire:navigate class="cr-abtn cr-accent" title="Редактировать">
                <i class="fa-solid fa-pen"></i>
            </a>
            <button type="button" wire:click="delete({{ $chapter->id }})" wire:confirm="Удалить главу «{{ $chapter->title }}»?" class="cr-abtn cr-err" title="Удалить">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>

    <div x-show="posOpen" x-cloak x-transition.opacity
         @click.outside="posOpen = false"
         class="chapter-row-jump">
        <span style="font-size:11px; color:var(--text-muted);">Перенести на позицию (1–{{ $groupTotal }}):</span>
        <input type="number" x-ref="posInput{{ $chapter->id }}" x-model.number="newPos"
               min="1" max="{{ $groupTotal }}"
               @keydown.enter="$wire.moveToPosition({{ $chapter->id }}, newPos); posOpen = false"
               @keydown.escape="posOpen = false; newPos = {{ $localPos }}"
               class="eri-input" style="width:80px; padding:5px 10px; font-size:13px; text-align:center;">
        <button type="button"
                @click="$wire.moveToPosition({{ $chapter->id }}, newPos); posOpen = false"
                class="cr-jump-go">
            Перенести
        </button>
        <button type="button" @click="posOpen = false; newPos = {{ $localPos }}" class="cr-jump-cancel">
            Отмена
        </button>
    </div>
</div>
