<?php

namespace App\Forum\Services;

use App\Forum\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ReportService
{
    public function __construct(private readonly LogService $logs) {}

    public function file(User $reporter, Model $target, string $reason, ?string $comment = null): Report
    {
        if (!in_array($reason, Report::REASONS, true)) {
            $reason = 'other';
        }
        $existing = Report::where('user_id', $reporter->id)
            ->where('reportable_type', get_class($target))
            ->where('reportable_id', $target->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) return $existing;

        $report = Report::create([
            'user_id'         => $reporter->id,
            'reportable_type' => get_class($target),
            'reportable_id'   => $target->id,
            'reason'          => $reason,
            'comment'         => $comment ? mb_substr($comment, 0, 1000) : null,
            'status'          => 'pending',
        ]);
        $this->logs->record($reporter, 'report.created', $target, ['report_id' => $report->id, 'reason' => $reason]);
        return $report;
    }

    public function resolve(User $moderator, Report $report, string $status): Report
    {
        if (!in_array($status, ['accepted', 'rejected'], true)) abort(422);
        $report->update([
            'status'      => $status,
            'resolved_at' => now(),
            'resolved_by' => $moderator->id,
        ]);
        $this->logs->record($moderator, 'report.resolved', $report, ['status' => $status]);
        return $report;
    }
}
