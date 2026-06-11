<?php
namespace App\Services;

use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageConverter {
    public static function convertToWebp($model, $attribute) {
        $path = $model->$attribute;
        
        if (!$path || !Storage::disk('s3')->exists($path)) return;
        if (Str::endsWith($path, '.webp')) return;

        if (!self::canConvertWebp()) {
            \Log::warning("WebP conversion skipped: GD not available or no WebP support.");
            return;
        }

        try {
            $contents = Storage::disk('s3')->get($path);

            if (class_exists(\Intervention\Image\Drivers\Gd\Driver::class)) {
                $driver = new \Intervention\Image\Drivers\Gd\Driver();
            } elseif (class_exists(\Intervention\Image\Drivers\Imagick\Driver::class)) {
                $driver = new \Intervention\Image\Drivers\Imagick\Driver();
            } else {
                \Log::warning("WebP conversion: no Intervention Image driver available.");
                return;
            }

            $manager = new ImageManager($driver);
            $image = $manager->read($contents);

            $newPath = preg_replace('/\.[^.]+$/', '', $path) . '.webp';
            $encoded = $image->toWebp(80);

            Storage::disk('s3')->put($newPath, $encoded->toString());

            if ($path !== $newPath && Storage::disk('s3')->exists($newPath) && Storage::disk('s3')->size($newPath) > 0) {
                Storage::disk('s3')->delete($path);
                $model->$attribute = $newPath;
                $model->saveQuietly();
            }

        } catch (\Exception $e) {
            \Log::error("WebP Conversion Error [{$path}]: " . $e->getMessage());
        }
    }

    private static function canConvertWebp(): bool {
        if (extension_loaded('gd')) {
            $info = gd_info();
            if (!empty($info['WebP Support'])) return true;
        }
        if (extension_loaded('imagick')) {
            $formats = \Imagick::queryFormats('WEBP');
            if (!empty($formats)) return true;
        }
        return false;
    }
}
