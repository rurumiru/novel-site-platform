@props([
    'url' => null,
    'title' => '',
    'size' => 'md',
])
@php
    $url = $url ?? url()->current();
    $encodedUrl = rawurlencode($url);
    $encodedTitle = rawurlencode($title);
    $tgHref  = "https://t.me/share/url?url={$encodedUrl}&text={$encodedTitle}";
    $vkHref  = "https://vk.com/share.php?url={$encodedUrl}&title={$encodedTitle}";
    $waHref  = "https://wa.me/?text={$encodedTitle}%20{$encodedUrl}";
    $xHref   = "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}";
    $fbHref  = "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
    $sizePx  = $size === 'sm' ? 32 : 36;
@endphp

<div
    x-data="{
        copied: false,
        copy() {
            const url = @js($url);
            const fallback = () => {
                try {
                    const ta = document.createElement('textarea');
                    ta.value = url;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                } catch (e) {}
            };
            const done = () => {
                this.copied = true;
                setTimeout(() => this.copied = false, 1800);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(done).catch(() => { fallback(); done(); });
            } else {
                fallback();
                done();
            }
        }
    }"
    {{ $attributes->merge(['style' => 'display:flex;flex-wrap:wrap;align-items:center;gap:8px;']) }}
>
    <a href="{{ $tgHref }}" target="_blank" rel="noopener noreferrer" title="Telegram"
       class="eri-btn ghost" style="width:{{ $sizePx }}px;height:{{ $sizePx }}px;padding:0;border-radius:50%;background:var(--surface-2);">
        <i class="fa-brands fa-telegram"></i>
    </a>
    <a href="{{ $vkHref }}" target="_blank" rel="noopener noreferrer" title="ВКонтакте"
       class="eri-btn ghost" style="width:{{ $sizePx }}px;height:{{ $sizePx }}px;padding:0;border-radius:50%;background:var(--surface-2);">
        <i class="fa-brands fa-vk"></i>
    </a>
    <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" title="WhatsApp"
       class="eri-btn ghost" style="width:{{ $sizePx }}px;height:{{ $sizePx }}px;padding:0;border-radius:50%;background:var(--surface-2);">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <a href="{{ $xHref }}" target="_blank" rel="noopener noreferrer" title="X"
       class="eri-btn ghost" style="width:{{ $sizePx }}px;height:{{ $sizePx }}px;padding:0;border-radius:50%;background:var(--surface-2);">
        <i class="fa-brands fa-x-twitter"></i>
    </a>
    <a href="{{ $fbHref }}" target="_blank" rel="noopener noreferrer" title="Facebook"
       class="eri-btn ghost" style="width:{{ $sizePx }}px;height:{{ $sizePx }}px;padding:0;border-radius:50%;background:var(--surface-2);">
        <i class="fa-brands fa-facebook-f"></i>
    </a>
    <button type="button" @click="copy()" :title="copied ? 'Скопировано!' : 'Скопировать ссылку'"
            class="eri-btn ghost" style="width:{{ $sizePx }}px;height:{{ $sizePx }}px;padding:0;border-radius:50%;background:var(--surface-2);">
        <i class="fa-solid" :class="copied ? 'fa-check' : 'fa-link'"></i>
    </button>
</div>
