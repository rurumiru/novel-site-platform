<?php
namespace App\Observers;
use App\Models\Chapter;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChapterObserver {
    public function saving(Chapter $chapter) {
        if ($chapter->isDirty('content')) {
            $chapter->content = $this->processImages($chapter->content);
        }
    }

    private function processImages($content) {
        $s3BaseUrl = rtrim(config('filesystems.disks.s3.url', ''), '/');

        $content = preg_replace_callback('/!\[(.*?)\]\((https?:\/\/[^\)]+)\)/', function($matches) {
            $s3Url = $this->downloadAndOptimize($matches[2]);
            return '![' . $matches[1] . '](' . $s3Url . ')';
        }, $content);

        $content = preg_replace_callback('/src=["\'](https?:\/\/[^"\']+)["\']/', function($matches) {
            $s3Url = $this->downloadAndOptimize($matches[1]);
            return 'src="' . $s3Url . '"';
        }, $content);

        $content = preg_replace_callback(
            '#(src=["\'])([^"\']*chapters_media/([A-Za-z0-9._-]+))(["\'])#',
            function ($m) use ($s3BaseUrl) {
                if (str_contains($m[2], 's3.')) return $m[0];
                return $m[1] . $s3BaseUrl . '/chapters_media/' . $m[3] . $m[4];
            },
            $content
        );

        $content = preg_replace_callback(
            '#(\]\()([^)]*chapters_media/([A-Za-z0-9._-]+))(\))#',
            function ($m) use ($s3BaseUrl) {
                if (str_contains($m[2], 's3.')) return $m[0];
                return $m[1] . $s3BaseUrl . '/chapters_media/' . $m[3] . $m[4];
            },
            $content
        );

        return $content;
    }

    private function downloadAndOptimize($url) {
        if (str_contains($url, 's3.')) {
            return $url;
        }

        if (preg_match('#chapters_media/([A-Za-z0-9._-]+)#', $url, $m)) {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            if (str_contains($url, '/storage/') || ($appHost && str_contains($url, $appHost))) {
                return rtrim(config('filesystems.disks.s3.url', ''), '/') . '/chapters_media/' . $m[1];
            }
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($appHost && str_contains($url, $appHost)) {
            return $url;
        }

        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (!$extension || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $extension = 'jpg';
                }

                $filename = 'chapters_media/' . Str::random(40) . '.' . $extension;
                Storage::disk('s3')->put($filename, $response->body());

                $optimized = ImageOptimizer::optimize($filename);
                Storage::disk('s3')->setVisibility($optimized, 'public');

                return Storage::disk('s3')->url($optimized);
            }
        } catch (\Exception $e) {
            \Log::warning("Chapter image download failed [{$url}]: " . $e->getMessage());
            return $url;
        }
        return $url;
    }
}
