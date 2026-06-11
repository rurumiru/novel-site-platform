<?php
namespace App\Observers;
use App\Models\Novel;
use App\Services\ImageConverter;

class NovelObserver {
    public function saved(Novel $novel) {
        if ($novel->wasChanged('cover_image')) {
            ImageConverter::convertToWebp($novel, 'cover_image');
        }
        if ($novel->wasChanged('background_image')) {
            ImageConverter::convertToWebp($novel, 'background_image');
        }
    }
}
