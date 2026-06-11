<?php

namespace App\Services;

use Illuminate\Support\Str;

class CommentSanitizer
{
    private const REPLACEMENT = 'тут была ссылка';

    public static function sanitize(string $content, bool $isAdmin = false): string
    {
        if ($isAdmin) {
            return $content;
        }

        $content = preg_replace('/<a\s+[^>]*>.*?<\/a>/uis', self::REPLACEMENT, $content);

        $content = preg_replace('/\[[^\]]*\]\([^\)]+\)/u', self::REPLACEMENT, $content);

        $content = preg_replace('/(?:https?:\/\/|www\.)[^\s\)\]"\'\s<>]+/ui', self::REPLACEMENT, $content);

        return trim($content);
    }
}
