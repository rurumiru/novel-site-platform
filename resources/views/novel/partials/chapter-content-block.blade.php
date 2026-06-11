<section class="chapter-block py-6" data-chapter-id="{{ $chapter->id }}">
    <h2 class="text-2xl md:text-3xl font-bold mb-6 text-center chapter-title">{{ $chapter->title }}</h2>
    <article class="prose prose-lg dark:prose-invert max-w-none leading-relaxed chapter-content" style="font-size: {{ (int)$fontSize }}px">
        {!! \App\Services\ContentRenderer::toHtml($chapter->content ?? '') !!}
    </article>
</section>
