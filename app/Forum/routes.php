<?php

use App\Forum\Http\Controllers\FeedController;
use App\Forum\Http\Controllers\ForumController;
use App\Forum\Http\Controllers\PostController;
use App\Forum\Http\Controllers\ReactionController;
use App\Forum\Http\Controllers\ReportController;
use App\Forum\Http\Controllers\SectionController;
use App\Forum\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

Route::prefix('forum')->name('forum.')->group(function () {

    Route::get('/', [ForumController::class, 'index'])->name('index');
    Route::get('/api/feed', [FeedController::class, 'index'])->name('feed');

    Route::get('/s/{section:slug}', [SectionController::class, 'show'])->name('sections.show');

    Route::middleware('auth')->group(function () {
        Route::get('/s/{section:slug}/threads/create', [ThreadController::class, 'create'])->name('threads.create');
        Route::post('/s/{section:slug}/threads',       [ThreadController::class, 'store'])->name('threads.store');
        Route::get('/s/{section:slug}/t/{thread}/edit', [ThreadController::class, 'edit'])->name('threads.edit');
        Route::put('/s/{section:slug}/t/{thread}',      [ThreadController::class, 'update'])->name('threads.update');
        Route::delete('/s/{section:slug}/t/{thread}',   [ThreadController::class, 'destroy'])->name('threads.destroy');
        Route::post('/s/{section:slug}/t/{thread}/pin',  [ThreadController::class, 'pin'])->name('threads.pin');
        Route::post('/s/{section:slug}/t/{thread}/lock', [ThreadController::class, 'lock'])->name('threads.lock');
        Route::post('/s/{section:slug}/t/{thread}/subscribe', [ThreadController::class, 'subscribe'])->name('threads.subscribe');
    });
    Route::get('/s/{section:slug}/t/{thread}', [ThreadController::class, 'show'])->name('threads.show');

    Route::middleware('auth')->group(function () {
        Route::post('/s/{section:slug}/t/{thread}/posts', [PostController::class, 'store'])->name('posts.store');
        Route::put('/posts/{post}',    [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

        Route::post('/threads/{thread}/react', [ReactionController::class, 'thread'])->name('threads.react');
        Route::post('/posts/{post}/react',     [ReactionController::class, 'post'])->name('posts.react');

        Route::post('/threads/{thread}/report', [ReportController::class, 'thread'])->name('threads.report');
        Route::post('/posts/{post}/report',     [ReportController::class, 'post'])->name('posts.report');

        Route::get('/moderation/reports',         [ReportController::class, 'index'])->name('moderation.reports');
        Route::post('/moderation/reports/{report}', [ReportController::class, 'resolve'])->name('moderation.reports.resolve');
    });
});
