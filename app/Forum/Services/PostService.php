<?php

namespace App\Forum\Services;

use App\Forum\Models\Post;
use App\Forum\Models\Thread;
use App\Forum\Support\MarkdownRenderer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly LogService $logs,
    ) {}

    public function create(User $author, Thread $thread, string $body, ?int $parentId = null): Post
    {
        return DB::transaction(function () use ($author, $thread, $body, $parentId) {
            if ($parentId) {
                $parentOk = Post::whereKey($parentId)->where('thread_id', $thread->id)->exists();
                if (!$parentOk) $parentId = null;
            }

            $post = Post::create([
                'thread_id' => $thread->id,
                'user_id'   => $author->id,
                'parent_id' => $parentId,
                'body'      => $body,
                'body_html' => MarkdownRenderer::render($body),
                'is_first'  => false,
            ]);

            $thread->increment('posts_count');
            $thread->update([
                'last_post_at'      => $post->created_at,
                'last_post_user_id' => $author->id,
            ]);

            $thread->subscribers()->syncWithoutDetaching([$author->id => ['notify' => true]]);

            $this->notifications->notifyNewReply($thread, $post);
            $this->logs->record($author, 'post.created', $post);

            return $post->fresh(['author', 'parent']);
        });
    }

    public function update(User $editor, Post $post, string $body): Post
    {
        $post->update([
            'body'      => $body,
            'body_html' => MarkdownRenderer::render($body),
            'edited_at' => now(),
            'edited_by' => $editor->id,
        ]);
        $this->logs->record($editor, 'post.updated', $post);
        return $post->fresh();
    }

    public function delete(User $actor, Post $post): void
    {
        if ($post->is_first) {
            $post->thread?->delete();
        } else {
            $post->delete();
            $post->thread?->decrement('posts_count');
        }
        $this->logs->record($actor, 'post.deleted', $post);
    }
}
