<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Models\Section;
use App\Forum\Models\Thread;
use App\Forum\Services\AccessService;
use Illuminate\Http\Request;

class SectionController extends BaseForumController
{
    public function __construct(private readonly AccessService $access) {}

    public function show(Request $request, Section $section)
    {
        if (!$this->access->canViewSection($request->user(), $section)) {
            abort(403, 'Раздел недоступен');
        }

        $threads = Thread::with(['author', 'lastPoster', 'tags', 'novel:id,title,slug'])
            ->where('section_id', $section->id)
            ->visibleTo($request->user())
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->paginate(20)
            ->withQueryString();

        return view('forum.sections.show', [
            'section'  => $section,
            'threads'  => $threads,
            'canCreate' => $request->user() && $this->access->canCreateThreadInSection($request->user(), $section),
        ]);
    }
}
