<?php
namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageOptimizer {
    public static function optimize($path, $maxWidth = 1200, $quality = 75) {
        if (!$path || !Storage::disk('s3')->exists($path)) return $path;

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['webp', 'svg'])) return $path;

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read(Storage::disk('s3')->get($path));

            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            $newPath = preg_replace('/\.[^.]+$/', '', $path) . '.webp';
            $encoded = $image->toWebp($quality);

            Storage::disk('s3')->put($newPath, $encoded->toString());

            if ($path !== $newPath) {
                Storage::disk('s3')->delete($path);
            }

            return $newPath;
        } catch (\Exception $e) {
            Log::error("Ошибка оптимизации {$path}: " . $e->getMessage());
            return $path;
        }
    }
}
