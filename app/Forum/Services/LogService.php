<?php

namespace App\Forum\Services;

use App\Forum\Models\ActionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LogService
{
    public function record(User $user, string $action, ?Model $subject = null, array $meta = []): void
    {
        ActionLog::create([
            'user_id'      => $user->id,
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'meta'         => $meta ?: null,
            'ip'           => request()?->ip(),
        ]);
    }
}
