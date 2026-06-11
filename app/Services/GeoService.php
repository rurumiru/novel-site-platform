<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoService {
    public static function isRu($ip): bool {
        if ($ip === '127.0.0.1' || $ip === '::1') return false;

        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            return strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) === 'RU';
        }

        return Cache::remember("geo_ip_{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    $data = $response->json();
                    return ($data['countryCode'] ?? '') === 'RU';
                }
            } catch (\Exception $e) {
                return false;
            }
            return false;
        });
    }
}
