<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Models\Category;
use App\Forum\Models\Thread;
use App\Forum\Services\FeedService;
use Illuminate\Http\Request;

class ForumController extends BaseForumController
{
    public function __construct(private readonly FeedService $feed) {}

    public function index(Request $request)
    {
        $categories = Category::with([
            'activeSections' => fn ($q) => $q->withCount('threads'),
        ])->where('is_active', true)->orderBy('sort_order')->get();

        $recentThreads = Thread::with(['section', 'author', 'lastPoster'])
            ->visibleTo($request->user())
            ->orderByDesc('last_post_at')
            ->limit(10)
            ->get();

        return view('forum.index', [
            'categories'     => $categories,
            'recentThreads'  => $recentThreads,
            'heroSnapshot'   => $this->feed->snapshot(),
        ]);
    }
}
