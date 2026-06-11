<?php

namespace App\Forum\Policies;

use App\Forum\Models\Thread;
use App\Forum\Services\AccessService;
use App\Models\User;

class ThreadPolicy
{
    public function __construct(private readonly AccessService $access) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Thread $thread): bool
    {
        return $this->access->canViewThread($user, $thread);
    }

    public function create(User $user, \App\Forum\Models\Section $section): bool
    {
        return $this->access->canCreateThreadInSection($user, $section);
    }

    public function update(User $user, Thread $thread): bool
    {
        if ($this->access->canModerate($user)) return true;
        return $thread->user_id === $user->id && $thread->created_at?->gt(now()->subHour());
    }

    public function delete(User $user, Thread $thread): bool
    {
        if ($this->access->canModerate($user)) return true;
        return $thread->user_id === $user->id && $thread->posts_count <= 1;
    }

    public function pin(User $user): bool
    {
        return $this->access->canModerate($user);
    }

    public function lock(User $user): bool
    {
        return $this->access->canModerate($user);
    }

    public function reply(User $user, Thread $thread): bool
    {
        return $this->access->canPostInThread($user, $thread);
    }
}
