<?php

namespace App\Forum\Services;

use App\Forum\Models\Post;
use App\Forum\Models\Section;
use App\Forum\Models\Tag;
use App\Forum\Models\Thread;
use App\Forum\Support\MarkdownRenderer;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ThreadService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly LogService $logs,
    ) {}

    public function create(User $author, Section $section, array $data): Thread
    {
        return DB::transaction(function () use ($author, $section, $data) {
            $novelId   = $data['novel_id'] ?? null;
            $chapterId = $data['chapter_id'] ?? null;

            if ($chapterId) {
                $chapter = Chapter::find($chapterId);
                if (!$chapter) abort(422, 'Глава не найдена');
                $novelId = $chapter->novel_id;
            }
            if ($novelId && !Novel::whereKey($novelId)->exists()) {
                abort(422, 'Новелла не найдена');
            }

            $thread = Thread::create([
                'section_id'        => $section->id,
                'user_id'           => $author->id,
                'novel_id'          => $novelId,
                'chapter_id'        => $chapterId,
                'title'             => trim($data['title']),
                'body'              => $data['body'] ?? null,
                'body_html'         => MarkdownRenderer::render($data['body'] ?? null),
                'visibility'        => $data['visibility'] ?? 'public',
                'allowed_roles'     => $data['allowed_roles'] ?? null,
                'last_post_at'      => now(),
                'last_post_user_id' => $author->id,
            ]);

            Post::create([
                'thread_id' => $thread->id,
                'user_id'   => $author->id,
                'body'      => $data['body'] ?? '',
                'body_html' => $thread->body_html,
                'is_first'  => true,
            ]);
            $thread->increment('posts_count');

            if (!empty($data['tags'])) {
                $this->syncTags($thread, $data['tags']);
            }

            $thread->subscribers()->syncWithoutDetaching([$author->id => ['notify' => true]]);

            $this->logs->record($author, 'thread.created', $thread);

            return $thread->fresh(['section', 'author', 'tags']);
        });
    }

    public function update(User $editor, Thread $thread, array $data): Thread
    {
        return DB::transaction(function () use ($editor, $thread, $data) {
            $changes = array_intersect_key($data, array_flip(['title', 'body', 'visibility', 'allowed_roles']));
            if (array_key_exists('body', $changes)) {
                $changes['body_html'] = MarkdownRenderer::render($changes['body'] ?? null);
            }
            $thread->update($changes);

            if (array_key_exists('body', $changes)) {
                $first = $thread->posts()->where('is_first', true)->first();
                if ($first) {
                    $first->update([
                        'body'      => $changes['body'],
                        'body_html' => $changes['body_html'],
                        'edited_at' => now(),
                        'edited_by' => $editor->id,
                    ]);
                }
            }

            if (array_key_exists('tags', $data)) {
                $this->syncTags($thread, $data['tags'] ?? []);
            }

            $this->logs->record($editor, 'thread.updated', $thread, ['fields' => array_keys($changes)]);
            return $thread->fresh();
        });
    }

    public function pin(User $mod, Thread $thread, bool $pinned): void
    {
        $thread->update(['is_pinned' => $pinned]);
        $this->logs->record($mod, $pinned ? 'thread.pinned' : 'thread.unpinned', $thread);
    }

    public function lock(User $mod, Thread $thread, bool $locked): void
    {
        $thread->update(['is_locked' => $locked]);
        $this->logs->record($mod, $locked ? 'thread.locked' : 'thread.unlocked', $thread);
    }

    public function softDelete(User $actor, Thread $thread): void
    {
        $thread->delete();
        $this->logs->record($actor, 'thread.deleted', $thread);
    }

    public function recordView(Thread $thread, ?User $user, string $sessionKey): void
    {
        $cacheKey = "forum:view:{$thread->id}:{$sessionKey}";
        if (\Cache::has($cacheKey)) return;
        \Cache::put($cacheKey, 1, now()->addHour());
        $thread->increment('views_count');
    }

    private function syncTags(Thread $thread, array $tagNames): void
    {
        $ids = [];
        foreach (array_slice(array_unique(array_map('trim', $tagNames)), 0, 8) as $name) {
            if ($name === '') continue;
            $slug = \Str::slug($name);
            if ($slug === '') continue;
            $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
            $ids[] = $tag->id;
        }
        $thread->tags()->sync($ids);

        Tag::whereIn('id', $ids)->each(function (Tag $t) {
            $t->update(['usage_count' => $t->threads()->count()]);
        });
    }
}
