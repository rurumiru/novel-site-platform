<?php

namespace App\Forum\Services;

use App\Forum\Models\Post;
use App\Forum\Models\Thread;
use App\Forum\Notifications\NewReplyNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyNewReply(Thread $thread, Post $post): void
    {
        $subs = $thread->subscribers()
            ->wherePivot('notify', true)
            ->where('users.id', '!=', $post->user_id)
            ->get();

        if ($subs->isEmpty()) return;

        Notification::send($subs, new NewReplyNotification($thread, $post));
    }
}
