<?php

namespace App\Forum\Services;

use App\Forum\Models\Section;
use App\Forum\Models\Thread;
use App\Models\User;

class AccessService
{
    public function canViewSection(?User $user, Section $section): bool
    {
        if (!$section->is_active) {
            return $user && $this->isModerator($user);
        }
        if (!$section->min_role) return true;
        return $user && $user->hasRole($section->min_role);
    }

    public function canCreateThreadInSection(?User $user, Section $section): bool
    {
        if (!$user) return false;
        if ($this->isBanned($user)) return false;

        return match ($section->create_policy) {
            'any'             => true,
            'auth'            => true,
            'role:author'     => $user->hasRole(['author', 'editor', 'moderator', 'deputy_admin', 'super_admin', 'owner']),
            'role:moderator'  => $this->isModerator($user),
            default           => true,
        };
    }

    public function canViewThread(?User $user, Thread $thread): bool
    {
        if ($thread->visibility === 'public') return $this->canViewSection($user, $thread->section);

        if (!$user) return false;
        if ($thread->user_id === $user->id) return true;
        if ($this->isModerator($user)) return true;

        if ($thread->visibility === 'private_users') {
            return $thread->participants()->where('users.id', $user->id)->exists();
        }
        if ($thread->visibility === 'private_roles') {
            $allowed = (array) ($thread->allowed_roles ?? []);
            foreach ($allowed as $r) if ($user->hasRole($r)) return true;
            return false;
        }
        return false;
    }

    public function canPostInThread(?User $user, Thread $thread): bool
    {
        if (!$user || $this->isBanned($user)) return false;
        if ($thread->is_locked && !$this->isModerator($user)) return false;
        if (!$this->canViewThread($user, $thread)) return false;

        if ($thread->visibility === 'private_users') {
            $row = $thread->participants()->where('users.id', $user->id)->first();
            if ($row && !$row->pivot->can_post && !$this->isModerator($user)) return false;
        }
        return true;
    }

    public function canEditPost(?User $user, \App\Forum\Models\Post $post): bool
    {
        if (!$user) return false;
        if ($this->isModerator($user)) return true;
        if ($post->user_id !== $user->id) return false;
        return $post->created_at?->gt(now()->subMinutes(30));
    }

    public function canDeletePost(?User $user, \App\Forum\Models\Post $post): bool
    {
        if (!$user) return false;
        if ($this->isModerator($user)) return true;
        if ($post->is_first) return false;
        return $post->user_id === $user->id;
    }

    public function canModerate(?User $user): bool
    {
        return $user !== null && $this->isModerator($user);
    }

    public function isModerator(User $user): bool
    {
        return $user->hasRole(['moderator', 'deputy_admin', 'super_admin', 'owner']);
    }

    public function isBanned(User $user): bool
    {
        if (!empty($user->is_banned)) return true;
        if (!empty($user->banned_until) && \Carbon\Carbon::parse($user->banned_until)->isFuture()) return true;
        return false;
    }
}
