<div wire:ignore x-data="{
    content: @entangle($attributes->wire('model')),
    init() {
        const quill = new Quill($refs.editor, {
            theme: 'snow',
            placeholder: 'Напишите главу здесь...',
            modules: {
                toolbar: [
                    [{ 'header': [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Установка начального значения
        if (this.content) {
            quill.root.innerHTML = this.content;
        }

        // Обновление Livewire при изменении
        quill.on('text-change', () => {
            this.content = quill.root.innerHTML;
        });

        // Обновление редактора, если Livewire изменил значение извне
        this.$watch('content', (newContent) => {
            if (newContent !== quill.root.innerHTML) {
                const range = quill.getSelection();
                quill.root.innerHTML = newContent;
                if (range) quill.setSelection(range); // Восстанавливаем курсор
            }
        });
    }
}">
    <div x-ref="editor" class="bg-white dark:bg-slate-900 rounded-b-xl"></div>
</div>
