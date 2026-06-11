<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Models\Post;
use App\Forum\Models\Section;
use App\Forum\Models\Thread;
use App\Forum\Services\AccessService;
use App\Forum\Services\PostService;
use Illuminate\Http\Request;

class PostController extends BaseForumController
{
    public function __construct(
        private readonly PostService $posts,
        private readonly AccessService $access,
    ) {}

    public function store(Request $request, Section $section, Thread $thread)
    {
        if ($thread->section_id !== $section->id) abort(404);
        \abort_unless($request->user(), 401);
        \abort_unless($this->access->canPostInThread($request->user(), $thread), 403);

        $data = $request->validate([
            'body'      => ['required', 'string', 'min:1', 'max:30000'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $this->posts->create($request->user(), $thread, $data['body'], $data['parent_id'] ?? null);

        return redirect()->to($thread->url() . '?page=last')->with('success', 'Сообщение отправлено');
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:30000'],
        ]);

        $this->posts->update($request->user(), $post, $data['body']);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'body_html' => $post->fresh()->body_html]);
        }
        return back()->with('success', 'Сообщение обновлено');
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);
        $this->posts->delete($request->user(), $post);

        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('success', 'Сообщение удалено');
    }
}
