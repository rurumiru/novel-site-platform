<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Models\Post;
use App\Forum\Models\Report;
use App\Forum\Models\Thread;
use App\Forum\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends BaseForumController
{
    public function __construct(private readonly ReportService $reports) {}

    public function thread(Request $request, Thread $thread)
    {
        \abort_unless($request->user(), 401);
        $data = $request->validate([
            'reason'  => ['required', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->reports->file($request->user(), $thread, $data['reason'], $data['comment'] ?? null);
        return response()->json(['ok' => true]);
    }

    public function post(Request $request, Post $post)
    {
        \abort_unless($request->user(), 401);
        $data = $request->validate([
            'reason'  => ['required', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->reports->file($request->user(), $post, $data['reason'], $data['comment'] ?? null);
        return response()->json(['ok' => true]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class);
        $reports = Report::with(['reporter', 'reportable'])
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->paginate(30);
        return view('forum.moderation.reports', compact('reports'));
    }

    public function resolve(Request $request, Report $report)
    {
        $this->authorize('resolve', $report);
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected']]);
        $this->reports->resolve($request->user(), $report, $data['status']);
        return back();
    }
}
