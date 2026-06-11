@php $allChapterIds = $chapters->pluck('id')->toArray(); @endphp
<div x-data="chapterManager()" data-chapter-ids='@json($allChapterIds)' wire:ignore.self>

    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            @if($chapters->count() > 0)
            <input type="checkbox"
                :checked="selectedIds.length > 0 && selectedIds.length === {{ count($allChapterIds) }}"
                :indeterminate="selectedIds.length > 0 && selectedIds.length < {{ count($allChapterIds) }}"
                @click="toggleSelectAll(@js($allChapterIds))"
                title="Выбрать все главы"
                style="cursor:pointer;">
            @endif
            <x-eriiba.chip variant="accent"><i class="fa-solid fa-book-open"></i> {{ $chapters->count() }} глав</x-eriiba.chip>
            <x-eriiba.btn size="sm" wire:click="addVolume" icon="folder-plus">Добавить том</x-eriiba.btn>
        </div>
        <div style="display:flex; gap:8px;">
            <x-eriiba.btn size="sm" wire:click="$set('showImport', true)" icon="file-import">Импорт</x-eriiba.btn>
            @if($chapters->count() > 0)
                <x-eriiba.btn size="sm" :href="route('author.chapter.create', $novel->id) . '?at=start'" wire:navigate icon="arrow-up-to-line">В начало</x-eriiba.btn>
            @endif
            <x-eriiba.btn variant="primary" size="sm" :href="route('author.chapter.create', $novel->id)" wire:navigate icon="plus">Добавить главу</x-eriiba.btn>
        </div>
    </div>

    @if($chapters->count() > 1)
    <div style="margin-bottom:12px; padding:8px 12px; background:var(--surface-2); border-radius:var(--r-sm); font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <i class="fa-solid fa-circle-info" style="color:var(--accent);"></i>
        <span><strong>Перетаскивание:</strong> зажмите ⋮⋮ и тяните в нужное место (можно между томами).</span>
        <span style="opacity:0.6;">·</span>
        <span><strong>Быстрая позиция:</strong> кликните на номер #N — введите новую позицию.</span>
        <span style="opacity:0.6;">·</span>
        <span><strong>Стрелки:</strong> ⇈ в начало, ▲ выше, ▼ ниже, ⇊ в конец.</span>
    </div>
    @endif

    @if(session('success'))
        <div class="eri-alert ok" style="margin-bottom:14px;"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div x-show="selectedIds.length > 0" x-cloak class="eri-alert" style="margin-bottom:14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <span style="font-weight:600; color:var(--accent);">
            <i class="fa-solid fa-check-square"></i>
            Выбрано: <span x-text="selectedIds.length"></span>
        </span>
        <x-eriiba.btn size="sm" variant="primary" @click="bulkMakeFreeAction()" icon="unlock">Сделать бесплатными</x-eriiba.btn>
        <x-eriiba.btn size="sm" @click="showPriceModal = true" icon="lock">Сделать платными</x-eriiba.btn>
        <x-eriiba.btn size="sm" variant="danger" @click="bulkDeleteAction()" icon="trash">Удалить</x-eriiba.btn>
        <x-eriiba.btn size="sm" @click="deselectAll()" icon="xmark">Отменить</x-eriiba.btn>
    </div>

    @if(($volumes ?? collect())->count() > 0)
    <div x-data="{ filterVolume: '', filterChapter: '' }" style="margin-bottom:14px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px;">
            <label class="eri-label" style="margin:0;">Том</label>
            <select x-model="filterVolume" @change="
                document.querySelectorAll('[data-volume-block]').forEach(el => {
                    el.style.display = (!filterVolume || el.dataset.volumeBlock === filterVolume || (filterVolume === '0' && el.dataset.volumeBlock === '0')) ? '' : 'none';
                })
            " class="eri-select" style="width:auto; padding:6px 28px 6px 10px;">
                <option value="">Все</option>
                @foreach($volumes ?? [] as $v)
                    <option value="{{ $v->id }}">{{ $v->title }}</option>
                @endforeach
                <option value="0">Без тома</option>
            </select>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:160px; max-width:300px;">
            <i class="fa-solid fa-search" style="color:var(--text-muted); font-size:12px;"></i>
            <input type="text" x-model="filterChapter" @input.debounce.200ms="
                const q = filterChapter.toLowerCase();
                document.querySelectorAll('[data-chapter-title]').forEach(el => {
                    el.style.display = (!q || el.dataset.chapterTitle.toLowerCase().includes(q)) ? '' : 'none';
                })
            " placeholder="Найти главу…" class="eri-input" style="padding:6px 10px;">
        </div>
    </div>
    @endif

    <div style="display:flex; flex-direction:column; gap:12px; max-height:680px; overflow-y:auto; padding-right:4px;">

        @foreach($volumes ?? [] as $volume)
            <div
                wire:key="volume-{{ $volume->id }}"
                data-volume-block="{{ $volume->id }}"
                x-data="{ renaming: false, vtitle: @js($volume->title) }"
                class="eri-card"
                style="overflow:hidden; padding:0; transition:border-color 0.15s, background 0.15s;"
            >
                <div style="padding:8px 12px; background:var(--surface-2); border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px; user-select:none;">
                    <i class="fa-solid fa-folder-open" style="color:var(--accent); font-size:12px; flex-shrink:0;"></i>
                    <span x-show="!renaming"
                          @dblclick.stop="renaming = true; $nextTick(() => $refs.vtitle{{ $volume->id }}.focus())"
                          style="font-weight:600; font-size:13px; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; min-width:0;"
                          title="Двойной клик для переименования"
                          x-text="vtitle"></span>
                    <span style="font-size:11px; color:var(--text-muted); flex-shrink:0;">{{ $volume->chapters->count() }} гл.</span>
                    <input x-show="renaming" x-ref="vtitle{{ $volume->id }}" x-model="vtitle"
                           @click.stop
                           @blur="$wire.renameVolume({{ $volume->id }}, vtitle); renaming = false"
                           @keydown.enter="$wire.renameVolume({{ $volume->id }}, vtitle); renaming = false"
                           @keydown.escape="renaming = false; vtitle = @js($volume->title)"
                           class="eri-input" style="padding:2px 6px; width:160px;" x-cloak>
                    <div style="display:flex; align-items:center; gap:2px; flex-shrink:0;">
                        <a href="{{ route('author.chapter.create', $novel->id) . '?volume=' . $volume->id }}" wire:navigate class="eri-btn sm" style="padding:5px 8px; color:var(--accent);" title="Добавить главу в этот том">
                            <i class="fa-solid fa-plus" style="font-size:10px;"></i>
                        </a>
                        <button type="button" wire:click="moveVolumeUp({{ $volume->id }})" class="eri-btn sm" style="padding:5px 7px;" title="Поднять том">
                            <i class="fa-solid fa-chevron-up" style="font-size:10px;"></i>
                        </button>
                        <button type="button" wire:click="moveVolumeDown({{ $volume->id }})" class="eri-btn sm" style="padding:5px 7px;" title="Опустить том">
                            <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                        </button>
                        <button type="button" wire:click="deleteVolume({{ $volume->id }})" wire:confirm="Удалить том? Главы переместятся в «Без тома»" class="eri-btn sm" style="padding:5px 7px; color:var(--err);" title="Удалить том">
                            <i class="fa-solid fa-trash" style="font-size:10px;"></i>
                        </button>
                    </div>
                </div>

                <div class="chapters-sortable-list"
                     data-volume-id="{{ $volume->id }}">
                    @forelse($volume->chapters as $chapter)
                        @include('livewire.author.partials.chapter-list-item')
                    @empty
                        <div class="empty-volume-slot" style="padding:18px; text-align:center; color:var(--text-muted); font-size:12px; font-style:italic;">
                            <i class="fa-regular fa-file-lines"></i> Нет глав — перетащите сюда главу из другого тома
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        @php $chaptersNoVol = $chapters->whereNull('volume_id'); @endphp
        @if($chaptersNoVol->count() > 0 || ($volumes ?? collect())->isEmpty())
            <div
                data-volume-block="0"
                class="eri-card"
                style="overflow:hidden; padding:0;"
            >
                <div style="padding:10px 14px; background:var(--surface-2); border-bottom:1px solid var(--border); font-size:12px; font-weight:600; color:var(--text-muted); display:flex; align-items:center; gap:8px;">
                    <i class="fa-regular fa-folder-open"></i> Без тома
                    <span style="font-weight:400; color:var(--text-muted);">({{ $chaptersNoVol->count() }})</span>
                </div>
                <div class="chapters-sortable-list"
                     data-volume-id="0">
                    @forelse($chaptersNoVol as $chapter)
                        @include('livewire.author.partials.chapter-list-item')
                    @empty
                        <div class="empty-volume-slot" style="padding:24px; text-align:center; color:var(--text-muted); font-size:12px; font-style:italic;">
                            <i class="fa-regular fa-file-lines"></i> Нет глав
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($chapters->count() === 0)
            <div style="text-align:center; padding:60px 16px; border:2px dashed var(--border); border-radius:var(--r-md);">
                <div style="width:56px; height:56px; background:var(--surface-2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--text-muted); font-size:22px;">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <p style="color:var(--text); font-weight:600; margin:0 0 4px;">Глав пока нет</p>
                <p style="color:var(--text-muted); font-size:13px; margin:0 0 18px;">Нажмите «Добавить главу» чтобы начать</p>
                <a href="{{ route('author.chapter.create', $novel->id) }}" wire:navigate class="eri-btn primary">
                    <i class="fa-solid fa-plus"></i> Создать первую главу
                </a>
            </div>
        @endif
    </div>

    @if($chapters->count() > 0)
        <div style="margin-top:14px; padding:14px; border:2px dashed var(--border); border-radius:var(--r-md); text-align:center;">
            <a href="{{ route('author.chapter.create', $novel->id) }}" wire:navigate class="eri-btn primary">
                <i class="fa-solid fa-plus"></i> Добавить главу в конец
            </a>
            <a href="{{ route('author.chapter.create', $novel->id) . '?at=start' }}" wire:navigate class="eri-btn" style="margin-left:8px;">
                <i class="fa-solid fa-arrow-up-to-line"></i> В начало списка
            </a>
        </div>
    @endif

    @if($showSplit && count($splitParagraphs) > 0)
    <div style="position:fixed; inset:0; z-index:300; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:16px;">
        <div class="eri-card" style="max-width:760px; width:100%; max-height:90vh; display:flex; flex-direction:column; padding:0;">
            <div style="padding:18px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-shrink:0;">
                <div>
                    <h2 style="font-family:var(--display); font-size:18px; font-weight:500; color:var(--text); margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-scissors" style="color:var(--warn);"></i> Разделить главу
                    </h2>
                    <p style="font-size:12px; color:var(--text-muted); margin:4px 0 0;">
                        Кликните между параграфами чтобы отметить точки разделения.
                        @if(count($splitPoints) > 0)
                            <strong style="color:var(--warn);">Выбрано точек: {{ count($splitPoints) }} (будет {{ count($splitPoints) + 1 }} частей)</strong>
                        @endif
                    </p>
                </div>
                <button type="button" wire:click="cancelSplit" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:none; border:none; color:var(--text-muted); cursor:pointer; flex-shrink:0;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="flex:1; overflow-y:auto; padding:18px;">
                @php $wordAccum = 0; $partNum = 1; @endphp
                @foreach($splitParagraphs as $i => $para)
                    @php $wordAccum += $para['words']; @endphp

                    @if($i > 0)
                        @php $isSplit = in_array($i, $splitPoints); @endphp
                        <button type="button" wire:click="toggleSplitPoint({{ $i }})"
                            style="width:100%; display:flex; align-items:center; gap:8px; padding:6px 8px; margin:2px 0; border-radius:var(--r-sm); transition:background 0.15s; background:{{ $isSplit ? 'color-mix(in srgb, var(--warn) 12%, transparent)' : 'transparent' }}; border:1px solid {{ $isSplit ? 'var(--warn)' : 'transparent' }}; cursor:pointer;">
                            @if($isSplit)
                                @php $partNum++; $wordAccum = 0; @endphp
                                <div style="flex:1; display:flex; align-items:center; gap:8px;">
                                    <div style="flex:1; height:1px; background:var(--warn);"></div>
                                    <span style="font-size:10px; font-weight:700; color:var(--warn); white-space:nowrap;"><i class="fa-solid fa-scissors"></i> РАЗРЕЗ {{ array_search($i, $splitPoints) + 1 }}</span>
                                    <div style="flex:1; height:1px; background:var(--warn);"></div>
                                </div>
                            @else
                                <div style="flex:1; display:flex; align-items:center; gap:8px; opacity:0.4;">
                                    <div style="flex:1; height:1px; background:var(--border);"></div>
                                    <span style="font-size:10px; color:var(--text-muted); white-space:nowrap;"><i class="fa-solid fa-plus"></i> разрезать тут</span>
                                    <div style="flex:1; height:1px; background:var(--border);"></div>
                                </div>
                            @endif
                        </button>

                        @if($isSplit)
                            <div style="font-size:10px; font-weight:700; color:var(--warn); text-transform:uppercase; padding:6px 8px;">Часть {{ $partNum }}</div>
                        @endif
                    @else
                        <div style="font-size:10px; font-weight:700; color:var(--ok); text-transform:uppercase; padding:6px 8px;">Часть 1</div>
                    @endif

                    <div style="padding:8px 12px; border-radius:var(--r-sm); font-size:13px; color:var(--text); line-height:1.5; {{ $i % 2 === 0 ? 'background:color-mix(in srgb, var(--surface-2) 60%, transparent);' : '' }}">
                        <span style="color:var(--text-muted); font-family:monospace; font-size:10px; margin-right:8px;">{{ $i + 1 }}</span>
                        {{ $para['text'] }}{{ mb_strlen($para['text'] ?? '') >= 300 ? '…' : '' }}
                        <span style="font-size:10px; color:var(--text-muted); margin-left:4px;">({{ $para['words'] }} сл.)</span>
                    </div>
                @endforeach
            </div>

            <div style="padding:18px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-shrink:0;">
                <span style="font-size:11px; color:var(--text-muted);">{{ count($splitParagraphs) }} параграфов, {{ collect($splitParagraphs)->sum('words') }} слов всего</span>
                <div style="display:flex; gap:8px;">
                    <x-eriiba.btn wire:click="cancelSplit">Отмена</x-eriiba.btn>
                    <x-eriiba.btn variant="primary" wire:click="executeSplit" :disabled="count($splitPoints) === 0" icon="scissors">
                        Разделить на {{ count($splitPoints) + 1 }} частей
                    </x-eriiba.btn>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showImport)
    <div style="position:fixed; inset:0; z-index:300; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:16px;">
        <div class="eri-card eri-card-pad-lg" style="max-width:600px; width:100%; max-height:90vh; overflow-y:auto; position:relative;">
            <button type="button" wire:click="$set('showImport', false)" style="position:absolute; top:14px; right:14px; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:none; border:none; color:var(--text-muted); cursor:pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h2 style="font-family:var(--display); font-size:20px; font-weight:500; color:var(--text); margin:0 0 4px;">Импорт глав</h2>
            <p style="color:var(--text-muted); font-size:13px; margin:0 0 18px;">TXT, FB2, EPUB, DOCX. Автоопределение глав по заголовкам.</p>

            <label style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:120px; border:2px dashed var(--border); border-radius:var(--r-sm); cursor:pointer; background:var(--surface-2); margin-bottom:8px;">
                <div wire:loading.remove wire:target="importFile" style="display:flex; flex-direction:column; align-items:center;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:24px; color:var(--text-muted); margin-bottom:8px;"></i>
                    <span style="font-size:13px; font-weight:600; color:var(--text);">{{ $importFile ? $importFile->getClientOriginalName() : 'Выберите файл' }}</span>
                    <span style="font-size:10px; color:var(--text-muted); margin-top:4px;">TXT, FB2, EPUB, DOCX — до 20 МБ</span>
                </div>
                <div wire:loading wire:target="importFile" style="display:flex; flex-direction:column; align-items:center;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; color:var(--accent); margin-bottom:8px;"></i>
                    <span style="font-size:13px; font-weight:600; color:var(--accent);">Загрузка…</span>
                </div>
                <input type="file" wire:model="importFile" accept=".txt,.fb2,.epub,.doc,.docx" style="display:none;">
            </label>
            @error('importFile') <span style="color:var(--err); font-size:12px; display:block; margin-bottom:8px;">{{ $message }}</span> @enderror

            @if($importFile && empty($importPreview))
            <x-eriiba.btn block wire:click="previewImport" wire:loading.attr="disabled" wire:target="previewImport">
                <span wire:loading.remove wire:target="previewImport"><i class="fa-solid fa-eye"></i> Предпросмотр глав</span>
                <span wire:loading wire:target="previewImport"><i class="fa-solid fa-spinner fa-spin"></i> Анализ файла…</span>
            </x-eriiba.btn>
            @endif

            @if(!empty($importPreview))
            <div class="eri-alert ok" style="margin:14px 0;">
                <strong><i class="fa-solid fa-check-circle"></i> Найдено глав: {{ count($importPreview) }}</strong>
                <div style="margin-top:8px; max-height:200px; overflow-y:auto; display:flex; flex-direction:column; gap:4px;">
                    @foreach($importPreview as $i => $ch)
                    <div style="display:flex; align-items:center; gap:8px; font-size:12px; padding:4px 0; {{ $i > 0 ? 'border-top:1px solid color-mix(in srgb, var(--ok) 20%, transparent);' : '' }}">
                        <span style="font-weight:700; color:var(--ok); flex-shrink:0; min-width:20px; text-align:center;">{{ $i + 1 }}</span>
                        <span style="font-weight:500; color:var(--text); flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ch['title'] }}</span>
                        <span style="color:var(--text-muted); flex-shrink:0;">{{ number_format($ch['length']) }} зн.</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div style="display:flex; flex-direction:column; gap:8px; margin:14px 0;">
                <div>
                    <label class="eri-label">Том</label>
                    <select wire:model="importVolumeId" class="eri-select">
                        <option value="">Без тома</option>
                        @foreach($novel->volumes()->orderBy('sort_order')->get() as $v)
                            <option value="{{ $v->id }}">{{ $v->title }}</option>
                        @endforeach
                    </select>
                </div>

                <label style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--surface-2); border-radius:var(--r-sm); cursor:pointer;">
                    <span style="font-weight:600; color:var(--text); font-size:13px;">Сделать платными</span>
                    <input type="checkbox" wire:model.live="importIsLocked">
                </label>
                @if($importIsLocked)
                    <div style="display:flex; align-items:center; gap:8px; padding:10px 14px; background:var(--surface-2); border-radius:var(--r-sm);">
                        <span style="font-size:13px; color:var(--text-muted); flex-shrink:0;">Цена (₽)</span>
                        <input type="number" wire:model="importPrice" min="1" class="eri-input" style="width:100px;">
                    </div>
                    @error('importPrice') <span style="color:var(--err); font-size:12px;">{{ $message }}</span> @enderror
                @endif
            </div>

            <x-eriiba.btn variant="primary" block wire:click="processImport" wire:loading.attr="disabled" wire:target="processImport">
                <span wire:loading.remove wire:target="processImport"><i class="fa-solid fa-file-import"></i> Импортировать{{ !empty($importPreview) ? ' (' . count($importPreview) . ' глав)' : '' }}</span>
                <span wire:loading wire:target="processImport"><i class="fa-solid fa-spinner fa-spin"></i> Импорт…</span>
            </x-eriiba.btn>
        </div>
    </div>
    @endif

    <div x-show="showPriceModal" x-cloak style="position:fixed; inset:0; z-index:300; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); padding:16px;">
        <div class="eri-card eri-card-pad-lg" style="max-width:380px; width:100%;">
            <h2 style="font-family:var(--display); font-size:18px; font-weight:500; color:var(--text); margin:0 0 4px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-lock" style="color:var(--warn);"></i> Сделать платными
            </h2>
            <p style="color:var(--text-muted); font-size:13px; margin:0 0 14px;">
                Укажите цену для <strong x-text="selectedIds.length" style="color:var(--text);"></strong> выбранных глав
            </p>
            <div style="margin-bottom:14px;">
                <label class="eri-label">Цена (₽)</label>
                <input type="number" x-model.number="bulkPrice" min="1" class="eri-input">
            </div>
            <div style="display:flex; gap:8px;">
                <x-eriiba.btn block @click="showPriceModal = false">Отмена</x-eriiba.btn>
                <x-eriiba.btn variant="primary" block @click="bulkMakePaidAction()" x-bind:disabled="bulkPrice < 1" icon="lock">Применить</x-eriiba.btn>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // 1. Подгружаем SortableJS один раз
            if (typeof Sortable === 'undefined' && !document.querySelector('script[data-sortable-loader]')) {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                s.setAttribute('data-sortable-loader', '1');
                document.head.appendChild(s);
            }

            // 2. Глобальная функция инициализации всех sortable-списков на странице
            window.initChapterSortables = function() {
                if (typeof Sortable === 'undefined') {
                    setTimeout(window.initChapterSortables, 100);
                    return;
                }
                document.querySelectorAll('.chapters-sortable-list').forEach(el => {
                    // Если уже есть — пропускаем (data-sortable-ready проставлен)
                    if (el.dataset.sortableReady === '1') return;
                    el.dataset.sortableReady = '1';

                    Sortable.create(el, {
                        group: 'chapters',
                        handle: '.chapter-drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        forceFallback: false,
                        fallbackTolerance: 5,
                        onEnd: function(evt) {
                            const list = evt.to;
                            const volumeId = list.dataset.volumeId;
                            const orderedIds = [...list.querySelectorAll('[data-chapter-id]')]
                                .map(node => parseInt(node.dataset.chapterId))
                                .filter(Boolean);
                            if (!orderedIds.length) return;

                            // Находим Livewire-компонент
                            const wireEl = list.closest('[wire\\:id]');
                            const wireId = wireEl?.getAttribute('wire:id');
                            const component = window.Livewire?.find(wireId);
                            if (component) {
                                component.call('reorderGroup', orderedIds, volumeId === '0' ? null : parseInt(volumeId));
                            } else {
                                console.warn('[chapter-sortable] Livewire component not found for', wireId);
                            }
                        },
                    });
                });
            };

            // 3. Триггеры инициализации:
            //    - при загрузке страницы
            //    - после wire:navigate
            //    - после каждого morph (когда Livewire обновил DOM)
            document.addEventListener('DOMContentLoaded', window.initChapterSortables);
            document.addEventListener('livewire:navigated', function() {
                // После полной навигации сбрасываем флаги — все списки новые
                document.querySelectorAll('.chapters-sortable-list[data-sortable-ready]').forEach(el => {
                    delete el.dataset.sortableReady;
                });
                window.initChapterSortables();
            });

            // 4. Livewire 3 morph hook — после каждого UI-обновления переинициализируем
            if (window.Livewire) {
                hookLivewire();
            } else {
                document.addEventListener('livewire:init', hookLivewire);
            }
            function hookLivewire() {
                if (window._chSortHooked) return;
                window._chSortHooked = true;

                Livewire.hook('morph.added', () => {
                    requestAnimationFrame(window.initChapterSortables);
                });
                Livewire.hook('morphed', () => {
                    requestAnimationFrame(window.initChapterSortables);
                });
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => requestAnimationFrame(window.initChapterSortables));
                });
            }

            // 5. Первый запуск, на случай если событий не дождёмся
            requestAnimationFrame(window.initChapterSortables);
            setTimeout(window.initChapterSortables, 300);
            setTimeout(window.initChapterSortables, 1000);
        })();

        function chapterManager() {
            return {
                selectedIds: [],
                lastClickedId: null,
                showPriceModal: false,
                bulkPrice: 10,

                init() {
                    // Гарантируем что после mount sortable инициализируется
                    requestAnimationFrame(() => window.initChapterSortables && window.initChapterSortables());
                },

                getOrderedIds() {
                    try { return JSON.parse(this.$root.dataset.chapterIds || '[]'); }
                    catch { return []; }
                },

                isSelected(id) { return this.selectedIds.includes(id); },

                toggleSelect(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx > -1) this.selectedIds.splice(idx, 1);
                    else this.selectedIds.push(id);
                },

                handleSelect(id, event) {
                    if (event.shiftKey && this.lastClickedId !== null && this.lastClickedId !== id) {
                        const allIds = this.getOrderedIds();
                        const startIdx = allIds.indexOf(this.lastClickedId);
                        const endIdx   = allIds.indexOf(id);
                        if (startIdx !== -1 && endIdx !== -1) {
                            const from = Math.min(startIdx, endIdx);
                            const to   = Math.max(startIdx, endIdx);
                            const range = allIds.slice(from, to + 1);
                            for (const rid of range) {
                                if (!this.selectedIds.includes(rid)) this.selectedIds.push(rid);
                            }
                        }
                    } else {
                        this.toggleSelect(id);
                    }
                    this.lastClickedId = id;
                },

                toggleSelectAll(allIds) {
                    if (this.selectedIds.length === allIds.length) this.selectedIds = [];
                    else this.selectedIds = [...allIds];
                    this.lastClickedId = null;
                },

                deselectAll() { this.selectedIds = []; this.lastClickedId = null; },

                async bulkMakeFreeAction() {
                    if (!this.selectedIds.length) return;
                    if (!confirm(`Сделать ${this.selectedIds.length} глав бесплатными?`)) return;
                    await $wire.bulkMakeFree([...this.selectedIds]);
                    this.deselectAll();
                },

                async bulkMakePaidAction() {
                    const price = parseInt(this.bulkPrice);
                    if (!price || price < 1) return;
                    await $wire.bulkMakePaid([...this.selectedIds], price);
                    this.deselectAll();
                    this.showPriceModal = false;
                },

                async bulkDeleteAction() {
                    if (!this.selectedIds.length) return;
                    if (!confirm(`Удалить ${this.selectedIds.length} глав? Это действие необратимо!`)) return;
                    await $wire.bulkDelete([...this.selectedIds]);
                    this.deselectAll();
                },
            };
        }
    </script>

    <style>
        /* SortableJS визуалы */
        .sortable-ghost   { opacity: 0.4; background: var(--accent-soft) !important; }
        .sortable-chosen  { background: var(--accent-soft) !important; }
        .sortable-drag    { opacity: 0.95; box-shadow: 0 8px 24px rgba(0,0,0,0.18); cursor: grabbing !important; }
        .chapters-sortable-list { min-height: 30px; }
        .empty-volume-slot { background: repeating-linear-gradient(45deg, transparent, transparent 8px, var(--surface-2) 8px, var(--surface-2) 16px); }

        /* ─── CHAPTER ROW (новая строка главы) ─── */
        .chapter-row {
            border-bottom: 1px solid var(--border);
            background: transparent;
            transition: background 0.12s;
        }
        .chapter-row:hover { background: color-mix(in srgb, var(--accent) 4%, transparent); }

        .chapter-row-main {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            min-width: 0;
        }
        .chapter-row-check { cursor: pointer; width: 14px; height: 14px; margin: 0; flex-shrink: 0; }

        .chapter-row-drag {
            cursor: grab;
            color: var(--text-muted);
            background: none;
            border: 0;
            padding: 4px 2px;
            line-height: 0;
            flex-shrink: 0;
            touch-action: none;
        }
        .chapter-row-drag:hover { color: var(--accent); }

        .chapter-row-pos {
            flex-shrink: 0;
            min-width: 32px;
            height: 24px;
            padding: 0 8px;
            font-size: 12px;
            font-weight: 700;
            font-family: monospace;
            color: var(--text-muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.12s;
        }
        .chapter-row-pos:hover { color: var(--accent); border-color: var(--accent); background: var(--accent-soft); }
        .chapter-row-pos.is-accent { color: #b45309; background: #fef3c7; border-color: #fbbf24; }
        .chapter-row-pos.is-accent:hover { color: #92400e; border-color: #f59e0b; background: #fde68a; }

        .chapter-row-title {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            overflow: hidden;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .chapter-row-title:hover .chapter-row-title-text { color: var(--accent); text-decoration: underline; }
        .chapter-row-title-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        /* Цветовые статус-чипы (без иконок, только цвет) */
        .chapter-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-radius: 10px;
            flex-shrink: 0;
            line-height: 1.4;
            white-space: nowrap;
        }
        .pill-draft  { background: #fef3c7; color: #92400e; }
        .pill-paid   { background: #ffedd5; color: #9a3412; }
        .pill-err    { background: #fee2e2; color: #b91c1c; }
        .pill-sched  { background: #fef3c7; color: #b45309; }
        .pill-hl     { /* цвет inline */ }

        /* dark theme поддержка */
        [data-theme="dark"] .pill-draft { background: rgba(251,191,36,0.18); color: #fbbf24; }
        [data-theme="dark"] .pill-paid  { background: rgba(245,158,11,0.18); color: #fbbf24; }
        [data-theme="dark"] .pill-err   { background: rgba(248,113,113,0.18); color: #fca5a5; }
        [data-theme="dark"] .pill-sched { background: rgba(251,191,36,0.18); color: #fcd34d; }
        [data-theme="dark"] .chapter-row-pos.is-accent { color: #fbbf24; background: rgba(251,191,36,0.15); border-color: rgba(251,191,36,0.4); }

        /* Группа стрелок-перемещения */
        .chapter-row-move {
            display: inline-flex;
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .cr-mbtn {
            background: var(--surface);
            border: 0;
            border-left: 1px solid var(--border);
            padding: 5px 7px;
            font-size: 9px;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 26px;
        }
        .cr-mbtn:first-child { border-left: 0; }
        .cr-mbtn:hover { background: var(--accent-soft); color: var(--accent); }

        /* Селект тома */
        .chapter-row-vol {
            padding: 4px 22px 4px 8px;
            font-size: 11px;
            max-width: 110px;
            height: 26px;
            width: auto;
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* Action buttons */
        .chapter-row-actions {
            display: inline-flex;
            gap: 2px;
            flex-shrink: 0;
        }
        .cr-abtn {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0 8px;
            height: 26px;
            font-size: 11px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            line-height: 1;
        }
        .cr-abtn:hover { background: var(--accent-soft); }
        .cr-abtn.cr-warn   { color: #d97706; }
        .cr-abtn.cr-warn:hover { background: #fef3c7; color: #92400e; }
        .cr-abtn.cr-accent { color: var(--accent); }
        .cr-abtn.cr-err    { color: var(--err); }
        .cr-abtn.cr-err:hover { background: #fee2e2; color: #b91c1c; }

        /* Inline jump-form */
        .chapter-row-jump {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px 10px 56px;
            background: linear-gradient(90deg, color-mix(in srgb, #fbbf24 12%, transparent), transparent);
            border-top: 1px solid color-mix(in srgb, #fbbf24 30%, var(--border));
            flex-wrap: wrap;
        }
        .cr-jump-go {
            background: #f59e0b;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .cr-jump-go:hover { background: #d97706; }
        .cr-jump-cancel {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 12px;
            cursor: pointer;
        }
        .cr-jump-cancel:hover { background: var(--surface-2); color: var(--text); }

        /* Mobile: компактнее, разрешаем перенос */
        @media (max-width: 720px) {
            .chapter-row-main { flex-wrap: wrap; gap: 6px; padding: 8px 10px; }
            .chapter-row-title { flex: 1 0 100%; order: 5; padding: 4px 0; }
            .chapter-row-move, .chapter-row-vol, .chapter-row-actions { order: 6; }
        }
    </style>
</div>
