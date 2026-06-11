<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NovelApiController;
use App\Http\Controllers\Api\ChapterApiController;
use App\Http\Controllers\Api\UserApiController;

Route::prefix('v1')->group(function () {

    Route::get('/novels', [NovelApiController::class, 'index']);
    Route::get('/novels/{novel}', [NovelApiController::class, 'show']);
    Route::get('/novels/{novel}/chapters', [NovelApiController::class, 'chapters']);
    Route::get('/novels/{novel}/stats', [NovelApiController::class, 'stats']);

    Route::get('/chapters/{chapter}', [ChapterApiController::class, 'show']);

    Route::get('/authors', [UserApiController::class, 'index']);
    Route::get('/authors/{user}', [UserApiController::class, 'show']);
    Route::get('/authors/{user}/novels', [UserApiController::class, 'novels']);

    Route::get('/updates', [NovelApiController::class, 'updates']);

    Route::get('/genres', fn() => \App\Models\Genre::orderBy('name')->get(['id', 'name']));
    Route::get('/tags', fn() => \App\Models\Tag::orderBy('name')->get(['id', 'name']));

    Route::get('/search', [NovelApiController::class, 'search']);

    Route::get('/stats', function () {
        return response()->json([
            'novels'   => \App\Models\Novel::where('is_published', true)->count(),
            'chapters' => \App\Models\Chapter::where('is_published', true)->count(),
            'authors'  => \App\Models\User::whereHas('novels', fn($q) => $q->where('is_published', true))->count(),
            'views_today' => \App\Models\NovelView::where('viewed_at', now()->toDateString())->count(),
        ]);
    });
});
