<div class="editor-shell">
    <div class="editor-head">
        <div>
            <h1>Импорт глав</h1>
            <p style="color:var(--text-muted); margin:4px 0 0; font-size:13px;">{{ $novel->title }}</p>
        </div>
        <div class="spacer"></div>
        @if($step === 'split')
            <x-eriiba.btn wire:click="backToUpload" icon="arrow-left">Другой файл</x-eriiba.btn>
        @endif
        <x-eriiba.btn :href="route('author.novel.edit', $novel->id)" wire:navigate>Назад к новелле</x-eriiba.btn>
    </div>

    @if(session('error'))
        <div class="eri-alert err" style="margin-bottom:18px;"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    @if($step === 'upload')
    <div style="max-width:760px; margin:0 auto;">
        <div class="editor-grid cols-2">
            
            <section class="editor-section">
                <h2><i class="fa-solid fa-file-arrow-up" style="color:var(--accent); margin-right:6px;"></i>Загрузить файл</h2>
                <p style="font-size:11px; color:var(--text-muted); margin:0 0 14px;">TXT, FB2, EPUB, DOCX — до 20 МБ</p>

                <label style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:140px; border:2px dashed var(--border); border-radius:var(--r-sm); cursor:pointer; background:var(--surface-2);">
                    <div wire:loading.remove wire:target="file" style="display:flex; flex-direction:column; align-items:center;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size:24px; color:var(--text-muted); margin-bottom:8px;"></i>
                        <span style="font-size:13px; font-weight:600; color:var(--text);">{{ $file ? $file->getClientOriginalName() : 'Выберите файл' }}</span>
                    </div>
                    <div wire:loading wire:target="file" style="display:flex; flex-direction:column; align-items:center;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; color:var(--accent); margin-bottom:8px;"></i>
                        <span style="font-size:13px; font-weight:600; color:var(--accent);">Загрузка…</span>
                    </div>
                    <input type="file" wire:model="file" accept=".txt,.fb2,.epub,.doc,.docx" style="display:none;">
                </label>
                @error('file') <span style="color:var(--err); font-size:12px; display:block; margin-top:6px;">{{ $message }}</span> @enderror

                @if($file)
                <x-eriiba.btn variant="primary" block wire:click="parseFile" wire:loading.attr="disabled" wire:target="parseFile" style="margin-top:14px;">
                    <span wire:loading.remove wire:target="parseFile"><i class="fa-solid fa-arrow-right"></i> Распознать и продолжить</span>
                    <span wire:loading wire:target="parseFile"><i class="fa-solid fa-spinner fa-spin"></i> Анализ…</span>
                </x-eriiba.btn>
                @endif
            </section>

            <section class="editor-section">
                <h2><i class="fa-solid fa-paste" style="color:var(--ok); margin-right:6px;"></i>Вставить текст</h2>
                <p style="font-size:11px; color:var(--text-muted); margin:0 0 14px;">Скопируйте и вставьте</p>

                <textarea wire:model="pastedText" rows="6" class="eri-textarea" placeholder="Вставьте текст с главами…"></textarea>

                @if(mb_strlen($pastedText) > 50)
                <x-eriiba.btn variant="primary" block wire:click="parseText" wire:loading.attr="disabled" wire:target="parseText" style="margin-top:14px;">
                    <span wire:loading.remove wire:target="parseText"><i class="fa-solid fa-arrow-right"></i> Распознать и продолжить</span>
                    <span wire:loading wire:target="parseText"><i class="fa-solid fa-spinner fa-spin"></i> Анализ…</span>
                </x-eriiba.btn>
                @endif
            </section>
        </div>

        <section class="editor-section" style="margin-top:18px;">
            <h2>Поддерживаемые форматы</h2>
            <div class="editor-grid" style="grid-template-columns:repeat(4, 1fr);">
                @foreach([['TXT', 'Простой текст', 'fa-file-lines'], ['FB2', 'FictionBook XML', 'fa-file-code'], ['EPUB', 'Электронная книга', 'fa-book'], ['DOCX', 'Microsoft Word', 'fa-file-word']] as [$fmt, $desc, $icon])
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:var(--text-muted);">
                    <i class="fa-solid {{ $icon }}"></i>
                    <div><strong style="color:var(--text);">{{ $fmt }}</strong> — {{ $desc }}</div>
                </div>
                @endforeach
            </div>
            <p style="font-size:11px; color:var(--text-muted); margin:14px 0 0;">Автоопределение глав: «Глава №», «Chapter», «Part», «Часть», «Пролог», «Эпилог», «1. Название» и т.д.</p>
        </section>
    </div>

    @else
    
    <div class="editor-grid" style="grid-template-columns:3fr 1fr;">
        
        <section class="editor-section" style="display:flex; flex-direction:column; max-height:80vh; padding:0; overflow:hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid var(--border); flex-shrink:0; gap:12px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:var(--text-muted);">
                    <i class="fa-solid fa-file-lines" style="color:var(--accent);"></i>
                    <strong style="color:var(--text);">{{ $sourceInfo }}</strong>
                    <span>{{ number_format($totalParagraphs) }} п.</span>
                    <span>{{ number_format($totalWords) }} сл.</span>
                </div>
                <div style="display:flex; gap:6px;">
                    <x-eriiba.btn size="sm" wire:click="autoDetectSplits" icon="wand-magic-sparkles">Авто</x-eriiba.btn>
                    <x-eriiba.btn size="sm" wire:click="clearAllSplits" icon="eraser">Сбросить</x-eriiba.btn>
                </div>
            </div>

            <div style="flex:1; overflow-y:auto; padding:8px 18px;">
                @php $partNum = 1; @endphp
                @foreach($paragraphs as $i => $para)
                    @if($i > 0)
                        @php $isSplit = in_array($i, $splitPoints); @endphp
                        <button type="button" wire:click="toggleSplit({{ $i }})"
                            style="width:100%; display:flex; align-items:center; padding:4px 0; margin:2px 0; border-radius:var(--r-sm); transition:background 0.15s; background:{{ $isSplit ? 'color-mix(in srgb, var(--warn) 12%, transparent)' : 'transparent' }}; border:1px solid {{ $isSplit ? 'var(--warn)' : 'transparent' }}; cursor:pointer;">
                            @if($isSplit)
                                @php $partNum++; @endphp
                                <div style="flex:1; display:flex; align-items:center; gap:8px; padding:0 8px;">
                                    <div style="flex:1; height:1px; background:var(--warn);"></div>
                                    <span style="font-size:10px; font-weight:700; color:var(--warn); white-space:nowrap;"><i class="fa-solid fa-scissors"></i> Часть {{ $partNum }}</span>
                                    <div style="flex:1; height:1px; background:var(--warn);"></div>
                                </div>
                            @else
                                <div style="flex:1; display:flex; align-items:center; padding:0 8px; opacity:0;" class="hover-show">
                                    <div style="flex:1; height:1px; background:var(--border);"></div>
                                    <span style="font-size:9px; color:var(--text-muted); padding:0 8px;"><i class="fa-solid fa-plus"></i></span>
                                    <div style="flex:1; height:1px; background:var(--border);"></div>
                                </div>
                            @endif
                        </button>
                    @elseif(count($splitPoints) > 0)
                        <div style="font-size:10px; font-weight:700; color:var(--ok); text-transform:uppercase; padding:4px 8px;">Часть 1</div>
                    @endif

                    <div style="padding:6px 12px; font-size:13px; line-height:1.5; border-radius:4px; {{ $para['isHeader'] ? 'font-weight:700; color:var(--text); background:var(--accent-soft);' : 'color:var(--text);' }}">
                        <span style="color:var(--text-muted); font-size:10px; font-family:monospace; margin-right:4px;">{{ $i + 1 }}</span>{{ $para['preview'] }}{{ $para['trimmed'] ? '…' : '' }}
                        <span style="font-size:10px; color:var(--text-muted); margin-left:2px;">{{ $para['words'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <div style="display:flex; flex-direction:column; gap:14px;">
            <section class="editor-section">
                <h2 style="display:flex; justify-content:space-between; align-items:center; font-size:14px;">
                    <span><i class="fa-solid fa-list" style="color:var(--accent); margin-right:6px;"></i>Главы</span>
                    <x-eriiba.chip variant="accent">{{ count($chapters) }}</x-eriiba.chip>
                </h2>
                <div style="display:flex; flex-direction:column; gap:4px; max-height:240px; overflow-y:auto;">
                    @forelse($chapters as $i => $ch)
                    <div class="eri-card" style="display:flex; align-items:flex-start; gap:8px; padding:8px; font-size:11px;">
                        <span style="width:20px; height:20px; border-radius:4px; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; margin-top:1px;">{{ $i + 1 }}</span>
                        <div style="min-width:0;">
                            <div style="font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; line-height:1.3;">{{ $ch['title'] }}</div>
                            <div style="color:var(--text-muted); margin-top:2px;">{{ number_format($ch['words']) }} сл. / {{ $ch['paras'] }} п.</div>
                        </div>
                    </div>
                    @empty
                    <p style="font-size:11px; color:var(--text-muted); text-align:center; padding:10px 0; margin:0;">Весь текст = 1 глава</p>
                    @endforelse
                </div>
            </section>

            <section class="editor-section">
                <h2 style="font-size:14px;"><i class="fa-solid fa-cog" style="color:var(--text-muted); margin-right:6px;"></i>Настройки</h2>
                <div class="editor-grid" style="gap:10px;">
                    <div>
                        <label class="eri-label">Том</label>
                        <select wire:model="volumeId" class="eri-select">
                            <option value="">Без тома</option>
                            @foreach($volumes as $v)<option value="{{ $v->id }}">{{ $v->title }}</option>@endforeach
                        </select>
                    </div>

                    <label style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:var(--surface-2); border-radius:var(--r-sm); cursor:pointer;">
                        <span style="font-weight:600; color:var(--text); font-size:13px;">Платные</span>
                        <input type="checkbox" wire:model.live="isLocked">
                    </label>

                    @if($isLocked)
                    <div style="display:flex; align-items:center; gap:8px; padding:10px 12px; background:var(--surface-2); border-radius:var(--r-sm);">
                        <span style="font-size:13px; color:var(--text-muted);">Цена</span>
                        <input type="number" wire:model="price" min="1" class="eri-input" style="width:80px;">
                        <span style="font-size:13px; color:var(--text-muted);">₽</span>
                    </div>
                    @endif
                </div>
            </section>

            <x-eriiba.btn variant="primary" block wire:click="executeImport" wire:loading.attr="disabled" wire:target="executeImport">
                <span wire:loading.remove wire:target="executeImport"><i class="fa-solid fa-file-import"></i> Импортировать {{ count($chapters) }} {{ count($chapters) === 1 ? 'главу' : (count($chapters) < 5 ? 'главы' : 'глав') }}</span>
                <span wire:loading wire:target="executeImport"><i class="fa-solid fa-spinner fa-spin"></i> Импорт…</span>
            </x-eriiba.btn>
        </div>
    </div>
    @endif
</div>
