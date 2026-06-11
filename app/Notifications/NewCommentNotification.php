<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewCommentNotification extends Notification {
    use Queueable;

    public function __construct(public Comment $comment, public string $context = 'on_content') {}

    public function via($notifiable): array {
        return ['database'];
    }

    public function toDatabase($notifiable): array {
        $comment = $this->comment;
        $author  = $comment->user;
        $commentable = $comment->commentable;
        $commentableType = class_basename($comment->commentable_type ?? '');

        $targetTitle = null;
        $targetUrl   = null;

        if ($commentable instanceof \App\Models\Chapter) {
            $targetTitle = ($commentable->novel?->title ?? '—') . ' — ' . $commentable->title;
            $targetUrl   = $commentable->novel_id
                ? route('novel.read', [$commentable->novel_id, $commentable->id])
                : null;
        } elseif ($commentable instanceof \App\Models\Novel) {
            $targetTitle = $commentable->title;
            $targetUrl   = route('novel.show', $commentable->id);
        } elseif ($commentable instanceof \App\Models\Review) {
            $targetTitle = 'Обзор: ' . $commentable->title;
            $targetUrl   = route('reviews.show', $commentable->id);
        } elseif ($commentable) {
            $targetTitle = $commentableType;
        }

        $verb = $this->context === 'reply' ? 'ответил на ваш комментарий' : 'оставил комментарий';
        $location = $targetTitle ? " к «{$targetTitle}»" : '';

        return [
            'type'              => 'new_comment',
            'sub_type'          => $this->context,
            'message'           => ($author?->name ?? 'Кто-то') . " {$verb}{$location}",
            'content'           => Str::limit($comment->content, 600),
            'content_full'      => $comment->content,
            'comment_id'        => $comment->id,
            'commentable_type'  => $commentableType,
            'commentable_id'    => $comment->commentable_id,
            'target_title'      => $targetTitle,
            'target_url'        => $targetUrl,
            'author_id'         => $comment->user_id,
            'author_name'       => $author?->name,
            'author_avatar_url' => $author?->avatar_url,
        ];
    }
}
