<?php

namespace App\Forum\Http\Controllers;

use App\Forum\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends BaseForumController
{
    public function __construct(private readonly FeedService $feed) {}

    public function index(Request $request): JsonResponse
    {
        $limit = (int) min(max($request->integer('limit', 8), 4), 12);
        $data  = $this->feed->snapshot($limit);

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=15')
            ->header('X-Forum-Feed', 'v1');
    }
}
