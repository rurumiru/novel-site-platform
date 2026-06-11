
@php
    $novel = $chapter->novel;
    $href  = $novel ? route('novel.read', [$novel->id, $chapter->id]) : '#';
    $coverUrl = $novel?->cover_image ? \App\Models\Novel::storageUrl($novel->cover_image) : null;
    $novelHref = $novel ? route('novel.show', $novel->id) : '#';

    $totalChapters = $novel?->chapters_count
        ?? $novel?->getOriginal('chapters_count')
        ?? $novel?->chapters()->where('is_published', true)->count()
        ?? 0;
@endphp
<a href="{{ $href }}" wire:navigate class="upd-row">
    <div class="upd-cover">
        @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $novel?->title }}" loading="lazy" decoding="async">
        @else
            <i class="fa-solid fa-book"></i>
        @endif
        @if($novel?->is_adult)
            <span class="upd-adult">18+</span>
        @endif
    </div>

    <div class="upd-meta">
        <div class="upd-novel-title" title="{{ $novel?->title }}">{{ $novel?->title ?? '—' }}</div>
        <div class="upd-chapter-row">
            @if($chapter->is_locked)
                <i class="fa-solid fa-lock upd-lock" title="Платная глава"></i>
            @endif
            <span class="upd-chapter-title" title="{{ $chapter->title }}">{{ $chapter->title }}</span>
        </div>
        @if($novel?->publisher?->name)
            <div class="upd-author">
                <i class="fa-solid fa-feather"></i>
                {{ $novel->publisher->name }}
            </div>
        @endif
    </div>

    <div class="upd-side">
        <div class="upd-total">
            <span class="upd-total-num">{{ $totalChapters }}</span>
            <span class="upd-total-lbl">{{ trans_choice('глава|главы|глав', $totalChapters) }}</span>
        </div>
        <div class="upd-time">{{ $chapter->created_at?->diffForHumans(null, true, true) }}</div>
    </div>
</a>
