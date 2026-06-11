@php
    $fullHtml = \App\Services\ContentRenderer::toHtml($chapter->content ?? '');
    $totalLen = mb_strlen(strip_tags($fullHtml));
    $targetLen = $totalLen > 0 ? min(1500, max(400, (int) ($totalLen * 0.10))) : 0;

    $previewHtml = '';
    if ($targetLen > 0) {
        if (preg_match_all('/<p\b[^>]*>.*?<\/p>/is', $fullHtml, $matches) && !empty($matches[0])) {
            $accumulated = 0;
            $picked = [];
            foreach ($matches[0] as $para) {
                $picked[] = $para;
                $accumulated += mb_strlen(strip_tags($para));
                if ($accumulated >= $targetLen) break;
            }
            $previewHtml = implode("\n", $picked);
        } else {
            $plain = strip_tags($fullHtml);
            $previewHtml = '<p>' . nl2br(e(mb_substr($plain, 0, $targetLen))) . '…</p>';
        }
    }

    $words   = (int) round($totalLen / 5.5);
    $readMin = max(1, (int) round($words / 200));
    $when    = ($chapter->published_at ?? $chapter->created_at);
@endphp

@if(!empty($previewHtml))
    <div class="reader-paper-wrap" style="padding:0 0 24px;">
        <article class="reader-paper" style="position:relative;">
            <header class="reader-h">
                <div class="reader-h-num">Глава {{ $chapter->sort_order }}</div>
                <h1 class="display reader-h-title">{{ $chapter->title }}</h1>
                <div class="reader-h-meta">
                    <span>≈ {{ $readMin }} мин · {{ number_format($words) }} слов</span>
                    @if($when)
                        <span>·</span>
                        <span>{{ \Carbon\Carbon::parse($when)->diffForHumans() }}</span>
                    @endif
                </div>
            </header>

            <div class="reader-body chapter-content">
                {!! $previewHtml !!}
            </div>

            <div class="reader-preview-fade"></div>
        </article>
    </div>
@endif
