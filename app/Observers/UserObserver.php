<?php
namespace App\Observers;
use App\Models\User;
use App\Services\ImageConverter;

class UserObserver {
    public function saved(User $user) {
        if ($user->isDirty('avatar')) {
            ImageConverter::convertToWebp($user, 'avatar');
        }
        if ($user->isDirty('banner_image')) {
            ImageConverter::convertToWebp($user, 'banner_image');
        }
    }
}
