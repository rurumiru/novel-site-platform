@props([
    'novel' => null,
    'src' => null,
    'title' => null,
    'author' => null,
    'rank' => null,
    'lock' => false,
    'status' => null,
    'width' => null,
    'class' => '',
    'rounded' => null,
])
@php
    $resolvedSrc = $src;
    $resolvedTitle = $title ?? '';
    $resolvedAuthor = $author ?? '';
    $resolvedSeed = 0;

    if ($novel) {
        $resolvedTitle = $resolvedTitle ?: ($novel->title ?? '');
        $resolvedAuthor = $resolvedAuthor ?: ($novel->publisher->name ?? $novel->author ?? '');
        if (!$resolvedSrc && !empty($novel->cover_image)) {
            $resolvedSrc = \App\Models\Novel::storageUrl($novel->cover_image);
        }
        $resolvedSeed = $novel->id ?? 0;
    } else {
        $resolvedSeed = abs(crc32($resolvedTitle ?: 'x'));
    }

    $palettes = [
        ['#1f2a44','#3d5a96','#7ba4e8','#f4d35e'],
        ['#2c1810','#7a3b2e','#d97862','#f5e6d3'],
        ['#0f2b2b','#1d5454','#5fb3a8','#e8f0e8'],
        ['#2b1638','#5a2d6b','#c067a0','#f5d4e0'],
        ['#1a2b1f','#3d6b46','#86b572','#e8f0d4'],
        ['#291a0a','#6b3d1a','#c98a44','#f5e2c0'],
        ['#1a1a2e','#2d2d5f','#5a64a8','#e0e4f5'],
        ['#3a0e1f','#7a1f3a','#c44464','#f5d8e0'],
        ['#0a1f2e','#1f4868','#5a8fb3','#d8e8f0'],
        ['#241a2e','#4f3868','#8a6fb3','#dccdec'],
    ];
    $p = $palettes[$resolvedSeed % count($palettes)];
    $variant = $resolvedSeed % 5;
    $w = 200; $h = 300;
    $titleShort = mb_strlen($resolvedTitle) > 22 ? mb_substr($resolvedTitle, 0, 20) . '…' : $resolvedTitle;
    $style = $width ? "width:{$width}px;" : '';
    if ($rounded) $style .= "border-radius:{$rounded};";
@endphp
<div {{ $attributes->merge(['class' => 'eri-cover ' . $class]) }} @if($style) style="{{ $style }}" @endif>
    @if($rank)
        <div class="badge-rank @if($rank <= 3) gold @endif">#{{ $rank }}</div>
    @endif
    @if($lock)
        <div class="badge-lock"><i class="fa-solid fa-lock"></i></div>
    @endif
    @if($status)
        <div class="badge-status">{{ $status }}</div>
    @endif
    @if($resolvedSrc)
        <img src="{{ $resolvedSrc }}" alt="{{ $resolvedTitle }}" loading="lazy" decoding="async">
    @else
        
        <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="xMidYMid slice" class="cover-fill" style="width:100%;height:100%;display:block;">
            <defs>
                <linearGradient id="g{{ $resolvedSeed }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="{{ $p[0] }}"/>
                    <stop offset="1" stop-color="{{ $p[1] }}"/>
                </linearGradient>
            </defs>
            <rect width="{{ $w }}" height="{{ $h }}" fill="url(#g{{ $resolvedSeed }})"/>
            @if($variant === 0)
                <g>
                    <circle cx="{{ $w/2 }}" cy="{{ $h*0.36 }}" r="48" fill="{{ $p[2] }}" opacity="0.6"/>
                    <circle cx="{{ $w/2 }}" cy="{{ $h*0.36 }}" r="28" fill="{{ $p[3] }}" opacity="0.85"/>
                </g>
            @elseif($variant === 1)
                <g>
                    <path d="M0,{{ $h*0.7 }} Q{{ $w/2 }},{{ $h*0.45 }} {{ $w }},{{ $h*0.7 }} L{{ $w }},{{ $h }} L0,{{ $h }} Z" fill="{{ $p[2] }}" opacity="0.55"/>
                    <path d="M0,{{ $h*0.85 }} Q{{ $w/2 }},{{ $h*0.65 }} {{ $w }},{{ $h*0.85 }} L{{ $w }},{{ $h }} L0,{{ $h }} Z" fill="{{ $p[3] }}" opacity="0.4"/>
                </g>
            @elseif($variant === 2)
                <g>
                    <rect x="20" y="{{ $h*0.25 }}" width="{{ $w-40 }}" height="2" fill="{{ $p[3] }}" opacity="0.7"/>
                    <rect x="40" y="{{ $h*0.55 }}" width="{{ $w-80 }}" height="1" fill="{{ $p[2] }}" opacity="0.5"/>
                    <circle cx="{{ $w*0.75 }}" cy="{{ $h*0.35 }}" r="20" fill="none" stroke="{{ $p[3] }}" stroke-width="1.5" opacity="0.7"/>
                </g>
            @elseif($variant === 3)
                <g>
                    @for($i = 0; $i < 5; $i++)
                        <rect x="{{ 20 + $i*32 }}" y="{{ $h*0.5 - $i*8 }}" width="22" height="{{ 50 + $i*16 }}" fill="{{ $p[2] }}" opacity="{{ 0.4 + $i*0.1 }}"/>
                    @endfor
                </g>
            @else
                <g>
                    <path d="M{{ $w*0.3 }},{{ $h*0.2 }} L{{ $w*0.7 }},{{ $h*0.2 }} L{{ $w*0.85 }},{{ $h*0.5 }} L{{ $w*0.5 }},{{ $h*0.8 }} L{{ $w*0.15 }},{{ $h*0.5 }} Z" fill="none" stroke="{{ $p[3] }}" stroke-width="1.5" opacity="0.7"/>
                    <circle cx="{{ $w*0.5 }}" cy="{{ $h*0.5 }}" r="10" fill="{{ $p[3] }}" opacity="0.85"/>
                </g>
            @endif
            <rect x="0" y="{{ $h*0.78 }}" width="{{ $w }}" height="{{ $h*0.22 }}" fill="{{ $p[0] }}" opacity="0.55"/>
            <text x="{{ $w/2 }}" y="{{ $h*0.88 }}" text-anchor="middle"
                  font-family="Fraunces, serif" font-size="14" font-weight="600" fill="{{ $p[3] }}"
                  style="letter-spacing:-0.01em;">
                {{ $titleShort }}
            </text>
            <text x="{{ $w/2 }}" y="{{ $h*0.94 }}" text-anchor="middle"
                  font-family="Manrope, sans-serif" font-size="8" font-weight="500"
                  fill="{{ $p[2] }}" opacity="0.85" style="letter-spacing:0.05em;text-transform:uppercase;">
                {{ mb_strtoupper($resolvedAuthor) }}
            </text>
        </svg>
    @endif
</div>
