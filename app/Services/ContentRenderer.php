<?php

namespace App\Services;

use Illuminate\Support\Str;

class ContentRenderer
{
    private const MARKDOWN_MARKERS = '/(^|\n)(#{1,6}\s|>\s|[-*+]\s|\d+\.\s|```|---\s*$)|\*\*[^\s*][^*]*\*\*|!?\[[^\]]*\]\([^)]+\)/';

    public static function toHtml(?string $content): string
    {
        $content = trim($content ?? '');
        if ($content === '') {
            return '';
        }

        if (self::looksLikeHtml($content)) {
            return self::stripAttachmentCaptions(
                self::fixStorageUrls(
                    self::convertInlineMarkdownImages($content)
                )
            );
        }

        if (preg_match(self::MARKDOWN_MARKERS, $content)) {
            return self::stripAttachmentCaptions(self::fixStorageUrls((string) Str::markdown($content)));
        }

        return self::plainToHtml($content);
    }

    private static function convertInlineMarkdownImages(string $html): string
    {
        $protected = [];
        $tmp = preg_replace_callback(
            '#<(code|pre|a|img)\b[^>]*(?:/>|>.*?</\1>)#is',
            function ($m) use (&$protected) {
                $token = '@@ERIIBA_PROTECTED_' . count($protected) . '@@';
                $protected[$token] = $m[0];
                return $token;
            },
            $html
        );

        $tmp = preg_replace_callback(
            '/!\[([^\]]*)\]\(([^\s)]+)(?:\s+"([^"]+)")?\)/',
            function ($m) {
                $alt = htmlspecialchars($m[1] ?? '', ENT_QUOTES);
                $src = htmlspecialchars($m[2] ?? '', ENT_QUOTES);
                $title = !empty($m[3]) ? ' title="' . htmlspecialchars($m[3], ENT_QUOTES) . '"' : '';
                return '<img src="' . $src . '" alt="' . $alt . '"' . $title . ' loading="lazy" decoding="async">';
            },
            $tmp
        );

        foreach ($protected as $token => $orig) {
            $tmp = str_replace($token, $orig, $tmp);
        }

        return $tmp;
    }

    private static function stripAttachmentCaptions(string $html): string
    {
        $html = preg_replace('#<figcaption\b[^>]*>.*?</figcaption>#is', '', $html);

        $html = preg_replace('#<span\s+class=["\'][^"\']*attachment__(?:name|size|caption)[^"\']*["\'][^>]*>.*?</span>#is', '', $html);

        return $html;
    }

    public static function plainToHtml(?string $text): string
    {
        $text = trim($text ?? '');
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $blocks = preg_split('/\n{2,}/', $text);

        $html = [];
        $listBuffer = [];

        $flushList = function () use (&$html, &$listBuffer) {
            if (!empty($listBuffer)) {
                $items = array_map(fn($i) => '<li>' . nl2br(e($i)) . '</li>', $listBuffer);
                $html[] = '<ul>' . implode('', $items) . '</ul>';
                $listBuffer = [];
            }
        };

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') continue;

            $lines = preg_split('/\n/', $block);
            $isList = count($lines) > 0 && collect($lines)->every(fn($l) => preg_match('/^\s*-\s+/', $l));
            if ($isList) {
                foreach ($lines as $li) {
                    $listBuffer[] = preg_replace('/^\s*-\s+/', '', $li);
                }
                continue;
            }

            $flushList();

            if (preg_match('/^###\s+(.+)$/s', $block, $m)) {
                $html[] = '<h3>' . e(trim($m[1])) . '</h3>';
                continue;
            }
            if (preg_match('/^##\s+(.+)$/s', $block, $m)) {
                $html[] = '<h2>' . e(trim($m[1])) . '</h2>';
                continue;
            }

            if (preg_match('/^>\s?(.+)$/s', $block, $m)) {
                $inner = preg_replace('/^>\s?/m', '', $block);
                $html[] = '<blockquote><p>' . nl2br(e(trim($inner))) . '</p></blockquote>';
                continue;
            }

            $html[] = '<p>' . nl2br(e($block)) . '</p>';
        }

        $flushList();

        return implode("\n", $html);
    }

    private static function looksLikeHtml(string $content): bool
    {
        return (bool) preg_match('/<(?:p|div|span|br|strong|em|b|i|u|s|ul|ol|li|h[1-6]|img|blockquote|figure|pre|code|a)\b[^>]*>/i', $content);
    }

    private static function fixStorageUrls(string $html): string
    {
        $s3BaseUrl = rtrim(config('filesystems.disks.s3.url', ''), '/');

        $html = preg_replace_callback(
            '#(src=["\'])([^"\']*chapters_media/([A-Za-z0-9._-]+))(["\'])#',
            function ($m) use ($s3BaseUrl) {
                if (str_contains($m[2], 's3.')) return $m[0];
                return $m[1] . $s3BaseUrl . '/chapters_media/' . $m[3] . $m[4];
            },
            $html
        );

        return $html;
    }

    public static function htmlToMarkdown(?string $html): string
    {
        if (empty(trim($html ?? ''))) {
            return '';
        }
        if (!preg_match('/<[a-z][\s\S]*>/i', $html)) {
            return $html;
        }
        try {
            $converter = new \League\HTMLToMarkdown\HtmlConverter(['header_style' => 'atx', 'bold_style' => '**']);
            return $converter->convert($html);
        } catch (\Throwable $e) {
            return $html;
        }
    }

    public static function extractImages(?string $html): array
    {
        if (empty(trim($html ?? ''))) {
            return [];
        }
        $urls = [];
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            $urls = array_values(array_unique(array_filter($m[1], fn($u) => !empty(trim($u)))));
        }
        return $urls;
    }

    public static function excerpt(?string $content, int $length = 60): string
    {
        if (empty($content)) return '';
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        return Str::limit(trim($text), $length);
    }

    public static function normalizeEditorHtml(?string $html): string
    {
        $html = trim($html ?? '');
        if ($html === '') return '';

        $html = preg_replace('#^(?:\s*<p>(?:\s|&nbsp;|<br\s*/?>)*</p>\s*)+#i', '', $html);
        $html = preg_replace('#(?:\s*<p>(?:\s|&nbsp;|<br\s*/?>)*</p>\s*)+$#i', '', $html);

        $html = preg_replace('#(?:\s*<p>(?:\s|&nbsp;|<br\s*/?>)*</p>\s*){2,}#i', '<p><br></p>', $html);

        return trim($html);
    }
}
