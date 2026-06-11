@props([
    'variant' => null, // fantasy/romance/action/scifi/mystery/slice/horror/litrpg/accent/warn/danger
    'lg' => false,
    'href' => null,
])
@php
    $classes = 'eri-chip';
    if ($variant) $classes .= ' ' . $variant;
    if ($lg) $classes .= ' lg';
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
@endif
