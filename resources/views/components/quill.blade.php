@props(['model', 'label' => '', 'height' => 'h-96'])

<div wire:ignore class="w-full">
    @if($label) <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">{{ $label }}</label> @endif
    
    <div x-data="{
        content: @entangle($model),
        init() {
            if (typeof Quill === 'undefined') return;
            const quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: 'Пишите здесь...',
                modules: {
                    toolbar: [
                        [{ 'header': [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image', 'clean']
                    ]
                }
            });

            if (this.content) quill.root.innerHTML = this.content;

            quill.on('text-change', () => { this.content = quill.root.innerHTML; });

            // Слушаем событие обновления контента из Livewire
            Livewire.on('quill-content-updated', ({ content }) => {
                if (content !== quill.root.innerHTML) {
                    quill.root.innerHTML = content;
                }
            });
        }
    }">
        <div x-ref="editor" class="{{ $height }}"></div>
    </div>
</div>
