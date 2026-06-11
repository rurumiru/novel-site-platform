<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ScraperBlock
{
    private const BLOCK_TTL = 300;
    private const CACHE_KEY_PREFIX = 'scraper_blocked:';
    private const RATE_KEY_PREFIX = 'rate_limit:';
    private const RATE_LIMIT = 60;
    private const RATE_WINDOW = 60;

    private const BOT_UA_PATTERNS = [
        'python', 'requests', 'curl', 'wget', 'scrapy', 'lxml', 'beautifulsoup',
        'httpie', 'go-http-client', 'java/', 'okhttp', 'apache-httpclient',
        'bot', 'spider', 'crawler', 'headless', 'phantom', 'selenium',
        'playwright', 'puppeteer', 'got/', 'node-fetch', 'urllib',
        'httrack', 'offline', 'slurp', 'archiver', 'fetcher', 'harvest',
        'mechanize', 'aiohttp', 'httpx', 'axios/', 'superagent',
    ];

    private const SEARCH_ENGINE_PATTERNS = [
        'googlebot', 'bingbot', 'yandexbot', 'baiduspider', 'duckduckbot',
        'sogou', 'exabot', 'facebot', 'ia_archiver', 'mj12bot',
        'ahrefsbot', 'semrushbot', 'dotbot', 'rogerbot', 'sistrix',
        'screaming frog', 'seokicks', 'petalbot', 'bytespider',
        'applebot', 'twitterbot', 'linkedinbot', 'slackbot',
        'discordbot', 'telegrambot', 'whatsapp',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $fingerprint = $this->fingerprint($request);
        $ip = $request->ip();

        if ($this->isBlocked($fingerprint)) {
            return $this->blockedResponse($request);
        }

        if ($this->isSearchEngine($request)) {
            return $this->searchEngineResponse($request);
        }

        if ($this->isScraper($request)) {
            $this->block($fingerprint);
            return $this->blockedResponse($request);
        }

        if ($this->isRateLimited($ip)) {
            return response('Too Many Requests', 429)
                ->header('Retry-After', (string) self::RATE_WINDOW);
        }

        $this->incrementRate($ip);

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        return $response;
    }

    private function fingerprint(Request $request): string
    {
        $parts = [
            $request->ip(),
            $request->userAgent() ?? '',
            $request->header('Accept') ?? '',
            $request->header('Accept-Language') ?? '',
        ];
        return hash('sha256', implode('|', $parts));
    }

    private function isBlocked(string $fingerprint): bool
    {
        return Cache::has(self::CACHE_KEY_PREFIX . $fingerprint);
    }

    private function block(string $fingerprint): void
    {
        Cache::put(self::CACHE_KEY_PREFIX . $fingerprint, true, self::BLOCK_TTL);
    }

    private function shouldSkip(Request $request): bool
    {
        $path = $request->path();
        return $path === 'up'
            || str_starts_with($path, 'admin')
            || str_starts_with($path, 'livewire')
            || str_starts_with($path, '_ignition')
            || $path === 'robots.txt';
    }

    private function isSearchEngine(Request $request): bool
    {
        $ua = strtolower($request->userAgent() ?? '');
        foreach (self::SEARCH_ENGINE_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function isScraper(Request $request): bool
    {
        $ua = strtolower($request->userAgent() ?? '');

        if ($ua === '') {
            return true;
        }

        foreach (self::BOT_UA_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function isRateLimited(string $ip): bool
    {
        $key = self::RATE_KEY_PREFIX . $ip;
        $count = (int) Cache::get($key, 0);
        return $count >= self::RATE_LIMIT;
    }

    private function incrementRate(string $ip): void
    {
        $key = self::RATE_KEY_PREFIX . $ip;
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, self::RATE_WINDOW);
        }
    }

    private function blockedResponse(Request $request): Response
    {
        if ($request->expectsJson() || str_starts_with($request->path(), 'api')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        return response()->view('errors.scraper-blocked', [], 403);
    }

    private function searchEngineResponse(Request $request): Response
    {
        if ($request->expectsJson() || str_starts_with($request->path(), 'api')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return response('', 403)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
