<?php

namespace App\Services;

use App\Models\Sticker;
use Illuminate\Support\Facades\Cache;

class CommentContentRenderer
{
    private const ALLOWED_TAGS = '<b><i><u><s><strong><em><br><p><details><summary>';

    public static function toSafeHtml(?string $content): string
    {
        if (empty(trim($content ?? ''))) {
            return '';
        }
        $html = strip_tags($content, self::ALLOWED_TAGS);

        $html = preg_replace('/<details\b[^>]*>/i', '<details class="rv-spoiler">', $html);
        $html = preg_replace('/<summary\b[^>]*>/i', '<summary>', $html);

        $html = self::renderStickers($html);
        return $html;
    }

    private static function renderStickers(string $html): string
    {
        if (mb_strpos($html, '[sticker:') === false) return $html;

        $slugs = [];
        if (preg_match_all('/\[sticker:([a-z0-9_\-]+)\]/i', $html, $m)) {
            $slugs = array_unique($m[1]);
        }
        if (empty($slugs)) return $html;

        $map = Cache::remember('eri-stickers-map', 300, function () {
            return Sticker::where('is_active', true)->pluck('image_path', 'slug')->toArray();
        });

        return preg_replace_callback(
            '/\[sticker:([a-z0-9_\-]+)\]/i',
            function ($m) use ($map) {
                $slug = $m[1];
                if (!isset($map[$slug])) return $m[0];
                $url = \App\Models\Novel::storageUrl($map[$slug]);
                return '<img class="eri-sticker" src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt=":' . htmlspecialchars($slug, ENT_QUOTES) . ':" loading="lazy">';
            },
            $html
        );
    }
}
