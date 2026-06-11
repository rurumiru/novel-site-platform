<?php

namespace App\Forum\Notifications;

use App\Forum\Models\Post;
use App\Forum\Models\Thread;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReplyNotification extends Notification
{
    use Queueable;

    public function __construct(public Thread $thread, public Post $post) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'forum.new_reply',
            'thread_id'   => $this->thread->id,
            'thread_slug' => $this->thread->slug,
            'section'     => $this->thread->section?->slug,
            'title'       => $this->thread->title,
            'post_id'     => $this->post->id,
            'author'      => $this->post->author?->name,
            'preview'     => \Illuminate\Support\Str::limit(strip_tags($this->post->body_html ?? $this->post->body), 140),
        ];
    }
}
