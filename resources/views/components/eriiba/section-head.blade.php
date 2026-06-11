@props([
    'title' => '',
    'sub' => null,
    'more' => null, // url
    'moreLabel' => 'Все →',
])
<div {{ $attributes->merge(['class' => 'eri-section-head']) }}>
    <h2>{{ $title }}</h2>
    @if($sub)<span class="sub">{{ $sub }}</span>@endif
    @if($more)<a href="{{ $more }}" class="more" wire:navigate>{{ $moreLabel }}</a>@endif
    {{ $slot }}
</div>
