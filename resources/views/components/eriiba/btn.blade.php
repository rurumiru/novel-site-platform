@props([
    'variant' => null, // primary/ghost/danger
    'size' => null, // sm/lg
    'block' => false,
    'href' => null,
    'type' => 'button',
    'icon' => null,
])
@php
    $classes = 'eri-btn';
    if ($variant) $classes .= ' ' . $variant;
    if ($size) $classes .= ' ' . $size;
    if ($block) $classes .= ' block';
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<i class="fa-solid fa-{{ $icon }}"></i>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<i class="fa-solid fa-{{ $icon }}"></i>@endif
        {{ $slot }}
    </button>
@endif
