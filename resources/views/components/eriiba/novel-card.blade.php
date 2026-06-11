@props([
    'novel',
    'rank' => null,
    'genreLabel' => null,
    'genreVariant' => null, // fantasy/romance/...
])
@php
    $href = isset($novel->id) ? route('novel.show', $novel->id) : '#';
    $title = $novel->title ?? '';
    $author = $novel->publisher->name ?? $novel->author ?? ($novel->user->name ?? '');
    $rating = $novel->avg_rating ?? $novel->rating ?? 0;

    if (!$genreLabel && isset($novel->genres) && $novel->genres->count()) {
        $genreLabel = $novel->genres->first()->name ?? null;
    }
    if (!$genreVariant && $genreLabel) {
        $g = mb_strtolower($genreLabel);
        $map = [
            'фэнтези' => 'fantasy', 'fantasy' => 'fantasy',
            'роман' => 'romance', 'романтика' => 'romance', 'romance' => 'romance',
            'экшен' => 'action', 'боевик' => 'action', 'action' => 'action',
            'sci-fi' => 'scifi', 'фантастика' => 'scifi', 'scifi' => 'scifi',
            'мистика' => 'mystery', 'mystery' => 'mystery',
            'повседневность' => 'slice', 'slice' => 'slice',
            'хоррор' => 'horror', 'ужасы' => 'horror', 'horror' => 'horror',
            'litrpg' => 'litrpg', 'литрпг' => 'litrpg',
        ];
        foreach ($map as $needle => $variant) {
            if (mb_strpos($g, $needle) !== false) { $genreVariant = $variant; break; }
        }
    }
@endphp
<a href="{{ $href }}" wire:navigate class="eri-novel-card">
    <x-eriiba.cover :novel="$novel" :rank="$rank" />
    <div class="eri-novel-card-body">
        <div class="eri-novel-card-title">{{ $title }}</div>
        @if($author)<div class="eri-novel-card-author">{{ $author }}</div>@endif
        <div class="eri-novel-card-row">
            @if($genreLabel)
                <x-eriiba.chip :variant="$genreVariant">{{ $genreLabel }}</x-eriiba.chip>
            @endif
            @if($rating > 0)
                <x-eriiba.stars :value="$rating" />
                <span class="eri-novel-card-rating">{{ number_format((float) $rating, 1) }}</span>
            @endif
        </div>
    </div>
</a>
