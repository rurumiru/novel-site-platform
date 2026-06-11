<?php
namespace App\Observers;
use App\Models\AdBanner;
use App\Services\ImageConverter;

class AdBannerObserver {
    public function saved(AdBanner $banner) {
        if ($banner->isDirty('image')) {
            ImageConverter::convertToWebp($banner, 'image');
        }
    }
}
