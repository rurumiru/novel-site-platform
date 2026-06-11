@props(['model' => 'content', 'label' => '', 'height' => 'min-h-[300px]', 'placeholder' => 'Пишите текст в формате Markdown... *курсив*, **жирный**, # заголовок'])

@php $uid = 'md-' . uniqid(); @endphp
<div class="w-full" x-data="markdownToolbar('{{ $uid }}')">
    @if($label)
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">{{ $label }}</label>
    @endif
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 bg-white dark:bg-slate-900">
        <div class="flex flex-wrap gap-1 p-2 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700">
            <button type="button" @click="wrap('**','**')" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400" title="Жирный"><i class="fa-solid fa-bold text-sm"></i></button>
            <button type="button" @click="wrap('*','*')" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400" title="Курсив"><i class="fa-solid fa-italic text-sm"></i></button>
            <button type="button" @click="wrap('\n### ','')" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400 font-bold text-xs" title="Заголовок">H3</button>
            <button type="button" @click="wrap('\n> ','')" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400" title="Цитата"><i class="fa-solid fa-quote-right text-sm"></i></button>
            <button type="button" @click="wrap('\n- ','')" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400" title="Список"><i class="fa-solid fa-list-ul text-sm"></i></button>
            <button type="button" @click="insertLink()" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400" title="Ссылка"><i class="fa-solid fa-link text-sm"></i></button>
            <button type="button" @click="insertImage()" class="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400" title="Картинка"><i class="fa-solid fa-image text-sm"></i></button>
        </div>
        <textarea wire:model.live.debounce.500ms="{{ $model }}"
            id="{{ $uid }}"
            x-ref="ta"
            class="w-full {{ $height }} p-4 font-mono text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 bg-transparent resize-y focus:outline-none"
            placeholder="{{ $placeholder }}"
            spellcheck="true"
        ></textarea>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('markdownToolbar', (id) => ({
        get ta() { return document.getElementById(id); },
        wrap(before, after) {
            const ta = this.ta; if (!ta) return;
            const start = ta.selectionStart, end = ta.selectionEnd;
            const sel = ta.value.substring(start, end) || 'текст';
            const newText = before + sel + after;
            ta.value = ta.value.substring(0, start) + newText + ta.value.substring(end);
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            ta.focus();
            ta.setSelectionRange(start + before.length, start + before.length + sel.length);
        },
        insertLink() {
            const url = prompt('URL ссылки:', 'https://');
            if (url) this.wrap('[', '](' + url + ')');
        },
        insertImage() {
            const url = prompt('URL изображения:', 'https://');
            if (url) this.wrap('![', '](' + url + ')');
        }
    }));
});
</script>
