@props([
    'model' => 'content',
    'initialContent' => '',
    'height' => 'min-h-[400px]',
    'placeholder' => 'Текст главы. Поддерживается жирный, курсив, списки. Ctrl+V — вставка изображений из буфера.',
    'showImageButton' => true,
])
@php
    $raw = $initialContent ?? '';
    if (str_contains($raw, 'x-init=') || str_contains($raw, 'x-data=') || str_contains($raw, 'x-on:') || preg_match('/\}\)\.run\(\);\s*\}\s*\}\s*"/', $raw)) {
        $initialContent = '<p></p>';
    }
    $config = [
        'initialContent' => $initialContent,
        'placeholder' => $placeholder,
        'uploadUrl' => route('api.chapter.image.upload'),
        'model' => $model,
    ];
@endphp
<div
    x-data="tiptapChapterEditor()"
    x-init="init(); return () => { const e = window.__chapterEditor; if (e) { e.destroy(); window.__chapterEditor = null; } }"
    x-on:chapter-image-insert.window="insertImage($event.detail?.url ?? $event.detail)"
    data-config="{{ base64_encode(json_encode($config)) }}"
    class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 bg-white dark:bg-slate-900"
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
    <div x-ref="editorEl" class="{{ $height }} overflow-auto"></div>
</div>
