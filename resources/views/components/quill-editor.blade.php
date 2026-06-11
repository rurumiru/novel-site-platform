@props([
    'model' => 'content',
    'initialContent' => '',
    'height' => 'min-h-[60vh]',
    'placeholder' => 'Текст главы. Поддерживается жирный, курсив, списки. Ctrl+V — вставка изображений из буфера.',
])
@php
    $raw = $initialContent ?? '';
    if (str_contains($raw, 'x-init=') || str_contains($raw, 'x-data=') || str_contains($raw, 'x-on:')) {
        $initialContent = '';
    }

    $config = [
        'initialContent' => $initialContent,
        'placeholder'    => $placeholder,
        'uploadUrl'      => route('api.chapter.image.upload'),
        'model'          => $model,
    ];
@endphp

<style>
    .quill-chapter-wrapper { border: 1px solid rgb(226 232 240); border-radius: 0.75rem; background: #fff; transition: border-color 0.15s; }
    .dark .quill-chapter-wrapper { border-color: rgb(51 65 85); background: rgb(15 23 42); }
    .quill-chapter-wrapper:focus-within { border-color: rgb(99 102 241); }

    /* Sticky-фиксацию делает JS (makeToolbarSticky в quill-chapter.js) через
       position: fixed с placeholder'ом — CSS sticky на этом проекте не работает
       из-за overflow на каком-то предке (Filament/Livewire-морф). */
    .quill-chapter-wrapper .ql-toolbar.ql-snow {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        border: none;
        border-bottom: 1px solid rgb(226 232 240);
        /* Полностью непрозрачный фон — иначе при position:fixed через 3% alpha
           видно скроллящийся контент главы под тулбаром. */
        background: rgb(248 250 252);
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        padding: 8px;
    }
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow {
        background: rgb(15 23 42);
        border-bottom-color: rgb(51 65 85);
    }
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-stroke { stroke: rgb(203 213 225); }
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-fill { fill: rgb(203 213 225); }
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-picker-label { color: rgb(203 213 225); }
    .quill-chapter-wrapper .ql-toolbar.ql-snow button:hover .ql-stroke,
    .quill-chapter-wrapper .ql-toolbar.ql-snow button.ql-active .ql-stroke { stroke: rgb(99 102 241); }
    .quill-chapter-wrapper .ql-toolbar.ql-snow button:hover .ql-fill,
    .quill-chapter-wrapper .ql-toolbar.ql-snow button.ql-active .ql-fill { fill: rgb(99 102 241); }

    /* Кастомные undo/redo кнопки — у Quill нет дефолтных иконок для них. */
    .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-undo::after { content: '↶'; font-size: 16px; line-height: 1; }
    .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-redo::after { content: '↷'; font-size: 16px; line-height: 1; }
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-undo,
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-redo { color: rgb(203 213 225); }

    /* Кнопка «крючок» — fa-anchor через unicode. Формат у Quill чисто кастомный. */
    .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-hook::after { content: '⚓'; font-size: 15px; line-height: 1; }
    .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-hook {
        color: rgb(100 116 139);
    }
    .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-hook:hover,
    .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-hook.ql-active { color: rgb(99 102 241); }
    .dark .quill-chapter-wrapper .ql-toolbar.ql-snow .ql-hook { color: rgb(203 213 225); }

    /* Отображение крючков в самом редакторе — подчёркивание + light-bg. */
    .quill-chapter-wrapper .ql-editor .hook-mark {
        background: rgb(254 243 199);
        border-bottom: 1px dashed rgb(217 119 6);
        padding: 0 2px;
        border-radius: 2px;
        cursor: help;
    }
    .dark .quill-chapter-wrapper .ql-editor .hook-mark {
        background: rgb(120 53 15 / 0.35);
        border-bottom-color: rgb(251 191 36);
    }

    .quill-chapter-wrapper .ql-container.ql-snow {
        border: none;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        font-family: inherit;
        font-size: 1rem;
        /* Критично для sticky-тулбара: контейнер НЕ должен скроллиться сам.
           Иначе вся прокрутка идёт внутри .ql-editor (overflow-y:auto по дефолту),
           страница не двигается → sticky на toolbar никогда не активируется. */
        overflow: visible;
        height: auto;
    }
    .quill-chapter-wrapper .ql-editor {
        min-height: 60vh;
        height: auto;
        max-height: none;
        overflow: visible;
        padding: 1rem 1.25rem;
        line-height: 1.7;
        color: rgb(15 23 42);
    }
    .dark .quill-chapter-wrapper .ql-editor { color: rgb(226 232 240); }
    .quill-chapter-wrapper .ql-editor.ql-blank::before {
        color: rgb(148 163 184);
        font-style: normal;
        left: 1.25rem;
        right: 1.25rem;
    }
    .quill-chapter-wrapper .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 0.75rem 0;
    }
    .quill-chapter-wrapper .ql-editor h2 { font-size: 1.5rem; font-weight: 700; margin: 1rem 0 0.5rem; }
    .quill-chapter-wrapper .ql-editor h3 { font-size: 1.25rem; font-weight: 700; margin: 0.875rem 0 0.5rem; }
    .quill-chapter-wrapper .ql-editor blockquote {
        border-left: 4px solid rgb(99 102 241);
        padding: 0.25rem 0 0.25rem 1rem;
        margin: 0.75rem 0;
        color: rgb(71 85 105);
    }
    .dark .quill-chapter-wrapper .ql-editor blockquote { color: rgb(148 163 184); }

    /* Spinner overlay при загрузке картинки */
    .quill-chapter-wrapper .quill-upload-indicator {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 25;
        background: rgb(99 102 241);
        color: #fff;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
    }
</style>

<div
    wire:ignore
    x-data="{
        editor: null,
        ready: false,
        init() {
            const tryInit = () => {
                const fn = window.initChapterQuillEditor;
                if (!fn) {
                    if ((this._a = (this._a || 0) + 1) < 30) {
                        setTimeout(tryInit, 200);
                    } else {
                        console.warn('initChapterQuillEditor not loaded after 30 attempts');
                    }
                    return;
                }
                let cfg = {};
                try {
                    cfg = JSON.parse(atob(this.$el.dataset.config));
                } catch (e) {
                    console.warn('quill-editor: bad config', e);
                }
                this.editor = fn({
                    element: this.$refs.editorEl,
                    content: cfg.initialContent || '',
                    placeholder: cfg.placeholder || 'Текст главы...',
                    uploadUrl: cfg.uploadUrl || '/api/upload/chapter-image',
                    onUpdate: (html) => {
                        // Прямое присвоение в $wire-прокси → обновляет
                        // client-side значение БЕЗ network-round-trip.
                        // Реальный POST произойдёт при submit формы.
                        if (typeof $wire !== 'undefined') {
                            $wire[cfg.model || 'content'] = html;
                        }
                    },
                });
                this.ready = true;
            };
            tryInit();
        },
        destroy() {
            // Alpine вызывает при размонтировании. Чистим ссылки —
            // сам DOM убьёт Livewire navigate.
            if (this.editor && typeof this.editor.__cleanup === 'function') {
                try { this.editor.__cleanup(); } catch (e) {}
            }
            this.editor = null;
        },
    }"
    data-config="{{ base64_encode(json_encode($config)) }}"
    class="quill-chapter-wrapper relative"
>
    
    <div x-ref="editorEl"></div>
</div>
