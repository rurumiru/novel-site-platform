@props([
    'model' => 'content',
    'initialContent' => '',
    'height' => 'min-h-[400px]',
    'placeholder' => 'Введите текст главы...',
    'showImageButton' => true,
])
@php
    $editorId = 'trix-' . uniqid();
@endphp
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<div
    wire:ignore
    {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 bg-white dark:bg-slate-900']) }}
    x-data="{ tid: '{{ $editorId }}' }"
    @trix-change="$wire.set('{{ $model }}', document.getElementById(tid).value)"
    x-on:chapter-image-insert.window="
        const ed = document.querySelector('trix-editor[input=\'' + tid + '\']');
        if (ed && ed.editor) {
            const url = $event.detail?.url ?? $event.detail;
            const src = typeof url === 'string' ? url : (url?.url || url?.path);
            if (src) ed.editor.insertHTML('<img src=\"' + src + '\" class=\"max-w-full h-auto rounded-lg my-2\" loading=\"lazy\">');
        }
    "
>
    @if($showImageButton)
    <div class="flex justify-end px-2 py-1 border-b border-slate-100 dark:border-slate-800">
        <label class="cursor-pointer text-xs font-bold text-indigo-500 hover:text-indigo-600 flex items-center">
            <i class="fa-solid fa-image mr-1"></i> Вставить фото
            <input type="file" wire:model="image" class="hidden" accept="image/*">
        </label>
    </div>
    <div wire:loading wire:target="image" class="text-xs text-indigo-500 px-2 py-1">Загрузка...</div>
    @endif
    <input type="hidden" id="{{ $editorId }}">
    <script>
    (function(){
        var el = document.getElementById('{{ $editorId }}');
        if (el) el.value = {!! json_encode($initialContent) !!};
    })();
    </script>
    <trix-editor
        input="{{ $editorId }}"
        class="trix-content {{ $height }} p-4 text-slate-900 dark:text-slate-100 prose prose-lg dark:prose-invert max-w-none border-0"
        placeholder="{{ $placeholder }}"
    ></trix-editor>
</div>
<script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<script>document.addEventListener('trix-file-accept', function(e) { e.preventDefault(); });</script>
