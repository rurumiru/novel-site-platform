<?php

namespace App\Forum\Services;

use App\Forum\Models\Thread;
use App\Forum\Models\ThreadRead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function toggle(User $user, Thread $thread): bool
    {
        $exists = $thread->subscribers()->where('users.id', $user->id)->exists();
        if ($exists) {
            $thread->subscribers()->detach($user->id);
            return false;
        }
        $thread->subscribers()->attach($user->id, ['notify' => true]);
        return true;
    }

    public function markRead(User $user, Thread $thread, ?int $lastPostId = null): void
    {
        DB::table('forum_thread_reads')->updateOrInsert(
            ['user_id' => $user->id, 'thread_id' => $thread->id],
            ['last_read_post_id' => $lastPostId, 'last_read_at' => now()],
        );
    }

    public function hasUnread(User $user, Thread $thread): bool
    {
        $read = ThreadRead::where('user_id', $user->id)->where('thread_id', $thread->id)->first();
        if (!$read) return $thread->posts_count > 0;
        return $thread->last_post_at && $thread->last_post_at->gt($read->last_read_at);
    }
}
