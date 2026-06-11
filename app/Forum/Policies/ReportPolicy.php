<?php

namespace App\Forum\Policies;

use App\Forum\Models\Report;
use App\Forum\Services\AccessService;
use App\Models\User;

class ReportPolicy
{
    public function __construct(private readonly AccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $this->access->canModerate($user);
    }

    public function resolve(User $user, Report $report): bool
    {
        return $this->access->canModerate($user);
    }
}
