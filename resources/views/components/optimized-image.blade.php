@props(['src', 'alt' => '', 'class' => '', 'eager' => false, 'aspect' => null])
@php
    $eager = filter_var($eager ?? false, FILTER_VALIDATE_BOOLEAN);
@endphp
<img 
    src="{{ $src }}" 
    alt="{{ $alt }}"
    loading="{{ $eager ? 'eager' : 'lazy' }}"
    decoding="async"
    fetchpriority="{{ $eager ? 'high' : 'auto' }}"
    {{ $aspect ? 'style="aspect-ratio: ' . $aspect . '"' : '' }}
    {{ $attributes->merge(['class' => $class]) }}
>
