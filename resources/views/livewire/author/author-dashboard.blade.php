<div class="editor-shell">
    <div class="editor-head">
        <div>
            <h1>Кабинет автора</h1>
            <p style="color:var(--text-muted); margin:4px 0 0; font-size:14px;">Управление вашими работами и статистика</p>
        </div>
        <div class="spacer"></div>
        @php $errorCount = \App\Models\ChapterError::whereHas('chapter', fn($q) => $q->whereIn('novel_id', Auth::user()->novels()->pluck('id')->merge(Auth::user()->editedNovels()->pluck('novels.id'))->unique()))->where('status', 'new')->count(); @endphp
        <x-eriiba.btn :href="route('author.errors')" wire:navigate icon="triangle-exclamation">
            Ошибки
            @if($errorCount > 0)<x-eriiba.chip variant="warn">{{ $errorCount }}</x-eriiba.chip>@endif
        </x-eriiba.btn>
        @if($canCreate)
            <x-eriiba.btn variant="primary" wire:click="create" icon="plus">Новая новелла</x-eriiba.btn>
        @endif
    </div>

    @if(session('success'))
        <div class="eri-alert ok" style="margin-bottom:18px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="eri-alert err" style="margin-bottom:18px;">{{ session('error') }}</div>
    @endif

    <div class="editor-section">
        <h2>Статистика</h2>
        <div class="editor-grid" style="grid-template-columns:repeat(3, 1fr);">
            <div class="eri-card eri-card-pad-lg" style="display:flex; align-items:center; gap:14px;">
                <span style="width:48px; height:48px; border-radius:12px; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:18px;"><i class="fa-solid fa-book-open"></i></span>
                <div>
                    <div style="font-family:var(--display); font-size:24px; font-weight:600; color:var(--text);">{{ $stats['novels'] }}</div>
                    <div class="eri-label" style="margin:0;">Новелл</div>
                </div>
            </div>
            <div class="eri-card eri-card-pad-lg" style="display:flex; align-items:center; gap:14px;">
                <span style="width:48px; height:48px; border-radius:12px; background:color-mix(in srgb, var(--ok) 12%, transparent); color:var(--ok); display:flex; align-items:center; justify-content:center; font-size:18px;"><i class="fa-solid fa-file-lines"></i></span>
                <div>
                    <div style="font-family:var(--display); font-size:24px; font-weight:600; color:var(--text);">{{ $stats['chapters'] }}</div>
                    <div class="eri-label" style="margin:0;">Глав</div>
                </div>
            </div>
            <div class="eri-card eri-card-pad-lg" style="display:flex; align-items:center; gap:14px;">
                <span style="width:48px; height:48px; border-radius:12px; background:color-mix(in srgb, var(--warn) 12%, transparent); color:var(--warn); display:flex; align-items:center; justify-content:center; font-size:18px;"><i class="fa-solid fa-eye"></i></span>
                <div>
                    <div style="font-family:var(--display); font-size:24px; font-weight:600; color:var(--text);">{{ number_format($stats['views']) }}</div>
                    <div class="eri-label" style="margin:0;">Просмотров</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px;">
        <x-eriiba.btn :href="route('author.requests')" wire:navigate icon="inbox">Заявки</x-eriiba.btn>
        <x-eriiba.btn :href="route('author.stats')" wire:navigate icon="chart-line">Общая статистика</x-eriiba.btn>
    </div>

    <div class="editor-section">
        <h2>Мои работы</h2>
        @if($novels->isEmpty())
            <div style="text-align:center; padding:60px 16px; color:var(--text-muted); border:2px dashed var(--border); border-radius:var(--r-md);">
                <div style="font-size:38px; margin-bottom:12px; opacity:0.5;"><i class="fa-solid fa-book-open"></i></div>
                <p style="margin:0 0 18px;">У вас пока нет работ</p>
                @if($canCreate)
                    <x-eriiba.btn variant="primary" wire:click="create" icon="plus">Создать первую новеллу</x-eriiba.btn>
                @endif
            </div>
        @else
            <div class="lib-list">
                @foreach($novels as $novel)
                    <div class="eri-card" style="display:flex; flex-direction:column; gap:0; overflow:hidden;">
                        <div style="display:flex; gap:0; align-items:stretch;">
                            <div style="width:140px; aspect-ratio:2/3; flex-shrink:0; background:var(--surface-2); position:relative;">
                                @if($novel->cover_image)
                                    <img src="{{ \App\Models\Novel::storageUrl($novel->cover_image) }}" alt="{{ $novel->title }}" style="width:100%; height:100%; object-fit:cover;" loading="lazy">
                                @else
                                    <div style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--text-muted); font-size:32px;"><i class="fa-solid fa-book"></i></div>
                                @endif
                                <span style="position:absolute; top:8px; left:8px;">
                                    <x-eriiba.chip :variant="$novel->is_published ? 'accent' : (($novel->moderation_status ?? '') === 'pending' ? 'warn' : null)">
                                        {{ $novel->is_published ? 'Опубликовано' : (($novel->moderation_status ?? '') === 'pending' ? 'На модерации' : 'Черновик') }}
                                    </x-eriiba.chip>
                                </span>
                                @if($novel->is_editor ?? false)
                                    <span style="position:absolute; top:8px; right:8px;"><x-eriiba.chip variant="accent">Редактор</x-eriiba.chip></span>
                                @endif
                            </div>
                            <div style="flex:1; padding:16px; display:flex; flex-direction:column; justify-content:space-between; gap:12px; min-width:0;">
                                <div style="min-width:0;">
                                    <h3 style="font-family:var(--display); font-size:18px; font-weight:500; color:var(--text); margin:0 0 6px;">
                                        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate style="color:inherit; text-decoration:none;">{{ $novel->title }}</a>
                                    </h3>
                                    <div style="display:flex; flex-wrap:wrap; gap:14px; font-size:12px; color:var(--text-muted);">
                                        <span><i class="fa-solid fa-layer-group"></i> {{ $novel->chapters_count }} глав</span>
                                        <span><i class="fa-solid fa-eye"></i> {{ $novel->views }}</span>
                                        <span><i class="fa-solid fa-star" style="color:var(--warn);"></i> {{ $novel->average_rating }}</span>
                                    </div>
                                </div>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    <x-eriiba.btn variant="primary" size="sm" :href="route('author.novel.edit', $novel->id)" wire:navigate icon="pen">Редакт.</x-eriiba.btn>
                                    <x-eriiba.btn size="sm" :href="route('author.novel.stats', $novel->id)" wire:navigate icon="chart-line">Стат.</x-eriiba.btn>
                                    <x-eriiba.btn size="sm" :href="route('author.chapter.create', $novel->id)" wire:navigate icon="plus">Глава</x-eriiba.btn>
                                    @if($novel->user_id === Auth::id() || Auth::user()->hasRole('super_admin'))
                                        <x-eriiba.btn variant="danger" size="sm" wire:click="delete({{ $novel->id }})" wire:confirm="Удалить новеллу?" icon="trash">Удалить</x-eriiba.btn>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
