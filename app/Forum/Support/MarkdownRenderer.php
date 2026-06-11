<?php

namespace App\Forum\Support;

use Illuminate\Support\Str;

class MarkdownRenderer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'u', 's', 'code', 'pre', 'blockquote',
        'ul', 'ol', 'li', 'a', 'h2', 'h3', 'h4', 'hr', 'img',
    ];

    public static function render(?string $markdown): string
    {
        $markdown = trim((string) $markdown);
        if ($markdown === '') return '';

        $html = Str::markdown($markdown, [
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);

        return self::sanitize($html);
    }

    private static function sanitize(string $html): string
    {
        if ($html === '') return '';

        $doc = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('root');
        if (!$root) return strip_tags($html, '<' . implode('><', self::ALLOWED_TAGS) . '>');

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }

    private static function walk(\DOMNode $node): void
    {
        $allowed = array_flip(self::ALLOWED_TAGS);

        $children = iterator_to_array($node->childNodes);
        foreach ($children as $child) {
            if (!$child instanceof \DOMElement) continue;

            $tag = strtolower($child->tagName);

            if (!isset($allowed[$tag])) {
                while ($child->firstChild) {
                    $child->parentNode->insertBefore($child->firstChild, $child);
                }
                $child->parentNode->removeChild($child);
                continue;
            }

            $allowAttrs = match ($tag) {
                'a'   => ['href', 'title'],
                'img' => ['src', 'alt', 'title'],
                default => [],
            };

            $attrsSnapshot = [];
            foreach ($child->attributes as $a) {
                $attrsSnapshot[] = $a->nodeName;
            }
            foreach ($attrsSnapshot as $attrName) {
                if (!in_array($attrName, $allowAttrs, true)) {
                    $child->removeAttribute($attrName);
                }
            }

            if ($tag === 'a') {
                $href = $child->getAttribute('href');
                if (!self::isSafeUrl($href)) {
                    $child->removeAttribute('href');
                } else {
                    $child->setAttribute('rel', 'nofollow noopener');
                    $child->setAttribute('target', '_blank');
                }
            }
            if ($tag === 'img') {
                $src = $child->getAttribute('src');
                if (!self::isSafeUrl($src, allowScheme: ['http', 'https'])) {
                    $child->parentNode->removeChild($child);
                    continue;
                }
                $child->setAttribute('loading', 'lazy');
            }

            self::walk($child);
        }
    }

    private static function isSafeUrl(string $url, array $allowScheme = ['http', 'https', 'mailto']): bool
    {
        $url = trim($url);
        if ($url === '') return false;
        $parts = parse_url($url);
        if ($parts === false) return false;
        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme === '' && str_starts_with($url, '/')) return true;
        return in_array($scheme, $allowScheme, true);
    }
}
