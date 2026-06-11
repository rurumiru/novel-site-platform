<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\NovelResource;
use App\Models\User;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $users = User::whereHas('novels', fn($q) => $q->where('is_published', true))
            ->withCount(['novels' => fn($q) => $q->where('is_published', true)])
            ->latest()
            ->paginate($request->input('per_page', 20));

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        $user->loadCount(['novels' => fn($q) => $q->where('is_published', true)]);
        return new UserResource($user);
    }

    public function novels(User $user)
    {
        $novels = $user->novels()->where('is_published', true)->withAvg('ratings', 'score')->withCount('chapters')->with('tags')->paginate(12);
        return NovelResource::collection($novels);
    }
}
