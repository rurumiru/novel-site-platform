<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MinifyHtml {
    public function handle(Request $request, Closure $next): Response {
        $response = $next($request);

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return $response;
        }

        if (!$this->isHtml($response)) {
            return $response;
        }

        $content = $response->getContent();
        if (!$content) return $response;

        $preserved = [];

        $content = preg_replace_callback('/<(script|style|pre|textarea)\b[^>]*>.*?<\/\1>/is', function ($m) use (&$preserved) {
            $key = "\x1A" . count($preserved) . "\x1A";
            $preserved[$key] = $m[0];
            return $key;
        }, $content);

        $content = preg_replace_callback('/\bwire:(snapshot|effects)\s*=\s*(?:"([^"]*?)"|\'([^\']*?)\')/is', function ($m) use (&$preserved) {
            $key = "\x1A" . count($preserved) . "\x1A";
            $preserved[$key] = $m[0];
            return $key;
        }, $content);

        $content = preg_replace_callback('/(x-data|x-on:[a-z.\-:]+|x-init|x-bind:[a-z.\-:]+|x-trap[a-z.\-:]*|@[a-z.\-:]+)\s*=\s*"([^"]*)"/is', function ($m) use (&$preserved) {
            $key = "\x1A" . count($preserved) . "\x1A";
            $preserved[$key] = $m[0];
            return $key;
        }, $content);

        $original = $response->getContent();

        $content = preg_replace([
            '/<!--(?!\[if)(?:.|\s)*?-->/',
            '/>\s+/s',
            '/\s+</s',
            '/\s+/s',
        ], ['', '>', '<', ' '], $content);

        $content = strtr($content, $preserved);

        $extract = static function (string $html): array {
            preg_match_all(
                '/\bwire:(snapshot|effects)\s*=\s*(?:"([^"]*?)"|\'([^\']*?)\')/is',
                $html,
                $m
            );
            return $m[0] ?? [];
        };

        $before = $extract($original);
        $after  = $extract($content);

        if ($before !== $after) {
            \Log::error('MinifyHtml: wire:snapshot/effects drift detected — returning unminified HTML', [
                'url'           => $request->fullUrl(),
                'before_count'  => count($before),
                'after_count'   => count($after),
                'before_hashes' => array_map(fn($s) => substr(sha1($s), 0, 12), $before),
                'after_hashes'  => array_map(fn($s) => substr(sha1($s), 0, 12), $after),
                'first_diff'    => $this->firstDiff($before, $after),
            ]);
            $response->setContent($original);
            return $response;
        }

        $response->setContent($content);

        return $response;
    }

    private function firstDiff(array $before, array $after): array
    {
        $max = max(count($before), count($after));
        for ($i = 0; $i < $max; $i++) {
            $b = $before[$i] ?? null;
            $a = $after[$i] ?? null;
            if ($b !== $a) {
                return [
                    'index'    => $i,
                    'before'   => $b === null ? null : mb_substr($b, 0, 400),
                    'after'    => $a === null ? null : mb_substr($a, 0, 400),
                    'b_length' => $b === null ? null : strlen($b),
                    'a_length' => $a === null ? null : strlen($a),
                ];
            }
        }
        return [];
    }

    private function isHtml($response): bool {
        $type = $response->headers->get('Content-Type');
        return $type && str_contains($type, 'text/html');
    }
}
