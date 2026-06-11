<?php

namespace App\Forum\Policies;

use App\Forum\Models\Post;
use App\Forum\Services\AccessService;
use App\Models\User;

class PostPolicy
{
    public function __construct(private readonly AccessService $access) {}

    public function update(User $user, Post $post): bool
    {
        return $this->access->canEditPost($user, $post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->access->canDeletePost($user, $post);
    }

    public function react(User $user, Post $post): bool
    {
        return $this->access->canViewThread($user, $post->thread);
    }

    public function report(User $user, Post $post): bool
    {
        return !$this->access->isBanned($user);
    }
}
