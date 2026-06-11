
@php
    $allChapters = $novel ? $novel->chapters()->orderBy('sort_order')->get() : collect();
    $totalCh = $allChapters->count();
    $publishedCh = $allChapters->where('is_published', true)->count();
    $publishedPct = $totalCh > 0 ? round($publishedCh / $totalCh * 100) : 0;
    $volumes = $novel ? $novel->volumes()->with(['chapters' => fn($q) => $q->orderBy('sort_order')])->orderBy('sort_order')->get() : collect();
    $chaptersNoVol = $allChapters->whereNull('volume_id');
@endphp

<aside class="reader-side editor-side" x-data="{ tocQuery: '' }">
    <div class="reader-side-head">
        <a href="{{ route('my-novels') }}" wire:navigate class="reader-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            К моим новеллам
        </a>
        <div class="reader-side-title display">{{ $novel->title ?? 'Новый проект' }}</div>
        @if($novel)
            <div class="reader-side-author">{{ $novel->publisher?->name ?? 'Автор' }}</div>
        @endif
    </div>

    @if($novel && $totalCh > 0)
        <div class="reader-side-progress">
            <div class="reader-side-progress-bar">
                <div class="reader-side-progress-fill" style="width: {{ $publishedPct }}%"></div>
            </div>
            <div class="reader-side-progress-meta">
                <span>{{ $publishedCh }} из {{ $totalCh }} опубл.</span>
                <span>{{ $publishedPct }}%</span>
            </div>
        </div>

        <a href="{{ route('author.novel.edit', $novel->id) }}" wire:navigate
           class="toc-row {{ empty($currentChId) ? 'cur' : '' }}"
           style="margin: 6px; border-radius:var(--r-sm); grid-template-columns: 22px 1fr;">
            <i class="fa-solid fa-gear" style="color:var(--text-muted); font-size:12px;"></i>
            <span class="toc-title" style="font-family: var(--sans); font-weight: 600; font-size: 13px;">Настройки новеллы</span>
        </a>

        <div class="reader-side-search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" placeholder="Поиск глав" x-model="tocQuery">
        </div>

        <div class="reader-toc">
            @foreach($volumes as $vol)
                <div class="es-vol-pill">
                    <span class="es-vol-pill-name">{{ $vol->title }}</span>
                    <span class="es-vol-pill-count">{{ $vol->chapters->count() }}</span>
                </div>
                @foreach($vol->chapters as $localIdx => $ch)
                    @php $isCur = (int)$currentChId === (int)$ch->id; @endphp
                    <a href="{{ route('author.chapter.edit', [$novel->id, $ch->id]) }}" wire:navigate
                       class="toc-row {{ $isCur ? 'cur' : '' }}"
                       x-show="tocQuery === '' || '{{ \Illuminate\Support\Str::lower(addslashes($ch->title)) }}'.includes(tocQuery.toLowerCase())">
                        <span class="toc-num">{{ $localIdx + 1 }}</span>
                        <span class="toc-title">{{ $ch->title }}</span>
                        @if(!$ch->is_published)
                            <span class="toc-lock" title="Черновик">✎</span>
                        @elseif($ch->is_locked)
                            <span class="toc-lock" title="Платная">🔒</span>
                        @else
                            <span class="toc-check" title="Опубликована">✓</span>
                        @endif
                    </a>
                @endforeach
            @endforeach

            @if($chaptersNoVol->count() > 0)
                @if($volumes->count() > 0)
                    <div class="es-vol-pill es-vol-pill-novol">
                        <span class="es-vol-pill-name">Без тома</span>
                        <span class="es-vol-pill-count">{{ $chaptersNoVol->count() }}</span>
                    </div>
                @endif
                @foreach($chaptersNoVol->values() as $localIdx => $ch)
                    @php $isCur = (int)$currentChId === (int)$ch->id; @endphp
                    <a href="{{ route('author.chapter.edit', [$novel->id, $ch->id]) }}" wire:navigate
                       class="toc-row {{ $isCur ? 'cur' : '' }}"
                       x-show="tocQuery === '' || '{{ \Illuminate\Support\Str::lower(addslashes($ch->title)) }}'.includes(tocQuery.toLowerCase())">
                        <span class="toc-num">{{ $localIdx + 1 }}</span>
                        <span class="toc-title">{{ $ch->title }}</span>
                        @if(!$ch->is_published)
                            <span class="toc-lock" title="Черновик">✎</span>
                        @elseif($ch->is_locked)
                            <span class="toc-lock" title="Платная">🔒</span>
                        @else
                            <span class="toc-check" title="Опубликована">✓</span>
                        @endif
                    </a>
                @endforeach
            @endif

            <a href="{{ route('author.chapter.create', $novel->id) }}" wire:navigate
               class="toc-row editor-side-add"
               style="margin: 10px 6px 6px; padding: 10px 12px; background:var(--accent-soft); color:var(--accent);
                      grid-template-columns: 22px 1fr; border-radius:var(--r-sm); font-weight:600;">
                <i class="fa-solid fa-plus" style="font-size:12px;"></i>
                <span style="font-family:var(--sans); font-size:13px;">Добавить главу</span>
            </a>
        </div>
    @elseif($novel)
        <div style="padding: 18px; color: var(--text-muted); font-size: 13px; font-family: var(--serif);">
            Пока нет глав.
            <div style="margin-top: 14px;">
                <a href="{{ route('author.chapter.create', $novel->id) }}" wire:navigate class="eri-btn primary block">
                    <i class="fa-solid fa-plus"></i> Создать первую главу
                </a>
            </div>
        </div>
    @endif
</aside>
