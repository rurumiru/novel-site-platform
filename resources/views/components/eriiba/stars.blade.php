@props(['value' => 0, 'size' => 14])
@php
    $value = max(0, min(5, (float) $value));
    $full = floor($value);
    $half = ($value - $full) >= 0.5 ? 1 : 0;
@endphp
<span {{ $attributes->merge(['class' => 'eri-stars']) }} style="font-size:{{ $size }}px;">
    @for($i = 0; $i < 5; $i++)
        @if($i < $full)
            <i class="fa-solid fa-star"></i>
        @elseif($i === (int) $full && $half)
            <i class="fa-solid fa-star-half-stroke"></i>
        @else
            <i class="fa-regular fa-star" style="color:var(--text-faint)"></i>
        @endif
    @endfor
</span>
