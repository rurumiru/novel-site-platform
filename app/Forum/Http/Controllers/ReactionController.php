<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Models\Post;
use App\Forum\Models\Thread;
use App\Forum\Services\AccessService;
use App\Forum\Services\ReactionService;
use Illuminate\Http\Request;

class ReactionController extends BaseForumController
{
    public function __construct(
        private readonly ReactionService $reactions,
        private readonly AccessService $access,
    ) {}

    public function thread(Request $request, Thread $thread)
    {
        \abort_unless($request->user(), 401);
        \abort_unless($this->access->canViewThread($request->user(), $thread), 403);

        $data = $request->validate(['emoji' => ['required', 'string', 'max:32']]);
        $result = $this->reactions->toggle($request->user(), $thread, $data['emoji']);
        return response()->json($result);
    }

    public function post(Request $request, Post $post)
    {
        \abort_unless($request->user(), 401);
        \abort_unless($this->access->canViewThread($request->user(), $post->thread), 403);

        $data = $request->validate(['emoji' => ['required', 'string', 'max:32']]);
        $result = $this->reactions->toggle($request->user(), $post, $data['emoji']);
        return response()->json($result);
    }
}
