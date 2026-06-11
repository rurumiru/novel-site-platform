<?php
namespace App\Policies;
use App\Models\Novel;
use App\Models\User;

class NovelPolicy {
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Novel $novel): bool { return true; }
    
    public function create(User $user): bool { 
        return $user->hasRole(['super_admin', 'author', 'moderator']); 
    }
    
    public function update(User $user, Novel $novel): bool {
        if ($user->hasRole(['super_admin', 'moderator'])) return true;
        return $user->id === $novel->user_id || $novel->editors->contains($user);
    }
    
    public function delete(User $user, Novel $novel): bool { 
        return $user->hasRole('super_admin'); 
    }
}
