<div class="editor-with-sidebar">

    @include('livewire.author.partials.editor-sidebar', ['novel' => $novel, 'currentChId' => $chapterId])

    <div class="editor-main">
    @vite('resources/js/quill-chapter.js')
    <form wire:submit.prevent="save">
        
        <div class="editor-head" style="position:sticky; top:60px; z-index:30; background:var(--surface); padding-top:14px;">
            <div style="display:flex; align-items:center; gap:10px; min-width:0; flex:1;">
                <a href="{{ route('author.novel.edit', $novel->id) }}" wire:navigate
                   title="К списку глав"
                   style="width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; color:var(--text-muted); text-decoration:none; flex-shrink:0;">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div style="min-width:0;">
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">«{{ $novel->title }}»</div>
                    <h1 style="font-size:18px; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $chapterId ? 'Редактирование главы' : 'Новая глава' }}
                    </h1>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                @if($chapterId)
                    <div style="display:inline-flex; border:1px solid var(--border); border-radius:8px; overflow:hidden;">
                        @if($prevChapterId)
                            <a href="{{ route('author.chapter.edit', [$novel->id, $prevChapterId]) }}" wire:navigate
                               title="Предыдущая: {{ $prevChapterTitle }}"
                               style="padding:8px 10px; color:var(--text-muted); text-decoration:none; border-right:1px solid var(--border);">
                                <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                            </a>
                        @else
                            <span style="padding:8px 10px; color:var(--border); border-right:1px solid var(--border); cursor:not-allowed;"><i class="fa-solid fa-chevron-left" style="font-size:11px;"></i></span>
                        @endif
                        @if($nextChapterId)
                            <a href="{{ route('author.chapter.edit', [$novel->id, $nextChapterId]) }}" wire:navigate
                               title="Следующая: {{ $nextChapterTitle }}"
                               style="padding:8px 10px; color:var(--text-muted); text-decoration:none;">
                                <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                            </a>
                        @else
                            <span style="padding:8px 10px; color:var(--border); cursor:not-allowed;"><i class="fa-solid fa-chevron-right" style="font-size:11px;"></i></span>
                        @endif
                    </div>
                @endif
                <x-eriiba.btn type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk"></i> Сохранить</span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i> Сохранение…</span>
                </x-eriiba.btn>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="eri-alert ok" style="margin-bottom:18px;">{{ session('success') }}</div>
        @endif

        <section class="editor-section">
            <h2>Параметры главы</h2>
            {{ $this->form }}
        </section>

        <section class="editor-section">
            <h2>Текст главы <span style="color:var(--err);">*</span></h2>
            <x-quill-editor model="content" :initial-content="$content" />
            @error('content')
                <div style="margin-top:10px; color:var(--err); font-size:13px;">{{ $message }}</div>
            @enderror
        </section>
    </form>

    @if($chapterId)
        @php $versions = \App\Models\ChapterVersion::where('chapter_id', $chapterId)->with('user:id,name')->latest()->get(); @endphp
        @if($versions->isNotEmpty())
        <section class="editor-section">
            <h2><i class="fa-solid fa-clock-rotate-left" style="color:var(--text-muted); margin-right:6px;"></i> Версии текста ({{ $versions->count() }})</h2>
            <div class="editor-grid" style="gap:8px;">
                @foreach($versions as $version)
                <div x-data="{ expanded: false }" class="eri-card" style="overflow:hidden;">
                    <button type="button" @click="expanded = !expanded" style="width:100%; display:flex; align-items:center; gap:12px; padding:12px 14px; background:none; border:none; cursor:pointer; text-align:left; color:var(--text);">
                        <span style="width:32px; height:32px; border-radius:8px; background:var(--surface-2); color:var(--text-muted); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fa-solid fa-file-lines" style="font-size:13px;"></i>
                        </span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; font-weight:600; color:var(--text);">{{ $version->label ?? 'Версия' }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                {{ $version->user?->name ?? 'Система' }} · {{ $version->updated_at->format('d.m.Y H:i') }}
                                ({{ $version->updated_at->diffForHumans() }})
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:11px; color:var(--text-muted); transition:transform 0.2s;" :class="expanded ? 'fa-rotate-180' : ''"></i>
                    </button>
                    <div x-show="expanded" x-collapse style="padding:0 14px 14px;">
                        <div style="background:var(--surface-2); border-radius:var(--r-sm); padding:14px; max-height:260px; overflow-y:auto; font-size:12px; color:var(--text);">
                            {!! \App\Services\ContentRenderer::toHtml(Str::limit($version->content, 3000)) !!}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    @endif

    <x-filament-actions::modals />

    <div
        x-data="{
            open: false,
            index: 0,
            length: 0,
            text: '',
            body: '',
            init() {
                window.addEventListener('chapter-hook-open', (e) => {
                    const d = e.detail || {};
                    this.index = d.index ?? 0;
                    this.length = d.length ?? 0;
                    this.text = d.text || '';
                    this.body = d.body || '';
                    this.open = true;
                    this.$nextTick(() => {
                        const ta = document.getElementById('hook-body-textarea');
                        if (ta) ta.focus();
                    });
                });
            },
            save() {
                window.dispatchEvent(new CustomEvent('chapter-hook-apply', {
                    detail: { index: this.index, length: this.length, body: this.body.trim() },
                }));
                this.open = false;
            },
            remove() {
                window.dispatchEvent(new CustomEvent('chapter-hook-apply', {
                    detail: { index: this.index, length: this.length, body: '' },
                }));
                this.open = false;
            },
        }"
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        style="position:fixed; inset:0; z-index:80; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px); padding:16px;"
        @click.self="open = false"
    >
        <div class="eri-card eri-card-pad-lg" style="width:100%; max-width:520px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <span style="width:32px; height:32px; border-radius:8px; background:color-mix(in srgb, var(--warn) 16%, transparent); color:var(--warn); display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-anchor" style="font-size:13px;"></i>
                </span>
                <h3 style="font-family:var(--display); font-size:18px; font-weight:500; color:var(--text); margin:0;">Крючок к тексту</h3>
                <button type="button" @click="open = false" style="margin-left:auto; background:none; border:none; color:var(--text-muted); cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="margin-bottom:14px;">
                <div class="eri-label" style="margin-bottom:6px;">Выделенный фрагмент</div>
                <div class="eri-alert warn" style="font-style:italic;">
                    <span x-text="'«' + text + '»'"></span>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="eri-label">Доп. информация <span style="color:var(--text-muted); text-transform:none; font-weight:400;">(видна читателю при клике)</span></label>
                <textarea
                    id="hook-body-textarea"
                    x-model="body"
                    rows="5"
                    placeholder="Например: расшифровка термина, отсылка, примечание переводчика…"
                    class="eri-textarea"
                ></textarea>
                <p style="font-size:11px; color:var(--text-muted); margin:6px 0 0;">Поддерживается обычный текст, разрывы строк. HTML-теги будут экранированы.</p>
            </div>

            <div style="display:flex; gap:8px;">
                <x-eriiba.btn type="button" @click="open = false" block>Отмена</x-eriiba.btn>
                <x-eriiba.btn type="button" variant="danger" @click="remove()" x-show="body.length > 0"><i class="fa-solid fa-trash"></i></x-eriiba.btn>
                <x-eriiba.btn type="button" variant="primary" @click="save()" x-bind:disabled="!body.trim()" block>Сохранить</x-eriiba.btn>
            </div>
        </div>
    </div>
    </div>
</div>
