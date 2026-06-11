<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Models\Section;
use App\Forum\Models\Thread;
use App\Forum\Services\AccessService;
use App\Forum\Services\SubscriptionService;
use App\Forum\Services\ThreadService;
use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThreadController extends BaseForumController
{
    public function __construct(
        private readonly ThreadService $threads,
        private readonly SubscriptionService $subscriptions,
        private readonly AccessService $access,
    ) {}

    public function create(Request $request, Section $section)
    {
        \abort_unless($request->user(), 401);
        \abort_unless($this->access->canCreateThreadInSection($request->user(), $section), 403);

        $novel = null; $chapter = null;
        if ($id = $request->integer('novel_id')) $novel = Novel::find($id);
        if ($id = $request->integer('chapter_id')) {
            $chapter = Chapter::find($id);
            if ($chapter) $novel ??= $chapter->novel;
        }

        return view('forum.threads.create', [
            'section' => $section,
            'novel'   => $novel,
            'chapter' => $chapter,
        ]);
    }

    public function store(Request $request, Section $section)
    {
        \abort_unless($request->user(), 401);
        \abort_unless($this->access->canCreateThreadInSection($request->user(), $section), 403);

        $data = $request->validate([
            'title'         => ['required', 'string', 'min:3', 'max:200'],
            'body'          => ['required', 'string', 'min:5', 'max:30000'],
            'novel_id'      => ['nullable', 'integer', 'exists:novels,id'],
            'chapter_id'    => ['nullable', 'integer', 'exists:chapters,id'],
            'visibility'    => ['nullable', Rule::in(['public', 'private_users', 'private_roles'])],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string'],
            'tags'          => ['nullable', 'array', 'max:8'],
            'tags.*'        => ['string', 'max:40'],
        ]);

        $thread = $this->threads->create($request->user(), $section, $data);
        return redirect()->to($thread->url())->with('success', 'Тема создана');
    }

    public function show(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        \abort_unless($this->access->canViewThread($request->user(), $thread), 403);

        $this->threads->recordView(
            $thread,
            $request->user(),
            $request->user()?->id
                ? 'u' . $request->user()->id
                : 'sess_' . substr(md5($request->session()->getId()), 0, 8),
        );

        $posts = $thread->posts()
            ->with(['author', 'editor', 'parent.author', 'reactions'])
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        if ($request->user()) {
            $lastVisible = $posts->getCollection()->last();
            $this->subscriptions->markRead($request->user(), $thread, $lastVisible?->id);
        }

        return view('forum.threads.show', [
            'section'      => $section,
            'thread'       => $thread->load(['tags', 'novel:id,title,slug', 'chapter:id,title,novel_id', 'author']),
            'posts'        => $posts,
            'canReply'     => $request->user() && $this->access->canPostInThread($request->user(), $thread),
            'isSubscribed' => $request->user()
                ? $thread->subscribers()->where('users.id', $request->user()->id)->exists()
                : false,
        ]);
    }

    public function edit(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        $this->authorize('update', $thread);
        return view('forum.threads.edit', compact('section', 'thread'));
    }

    public function update(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        $this->authorize('update', $thread);

        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'body'  => ['required', 'string', 'min:5', 'max:30000'],
            'tags'  => ['nullable', 'array', 'max:8'],
            'tags.*' => ['string', 'max:40'],
        ]);

        $this->threads->update($request->user(), $thread, $data);
        return redirect()->to($thread->url())->with('success', 'Тема обновлена');
    }

    public function destroy(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        $this->authorize('delete', $thread);

        $this->threads->softDelete($request->user(), $thread);
        return redirect()->route('forum.sections.show', $section)->with('success', 'Тема удалена');
    }

    public function pin(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        $this->authorize('pin', Thread::class);
        $this->threads->pin($request->user(), $thread, !$thread->is_pinned);
        return back();
    }

    public function lock(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        $this->authorize('lock', Thread::class);
        $this->threads->lock($request->user(), $thread, !$thread->is_locked);
        return back();
    }

    public function subscribe(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        \abort_unless($request->user(), 401);
        \abort_unless($this->access->canViewThread($request->user(), $thread), 403);

        $subscribed = $this->subscriptions->toggle($request->user(), $thread);
        return response()->json(['subscribed' => $subscribed]);
    }
}
