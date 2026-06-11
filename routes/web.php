<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\RankingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Livewire\Author\MyNovels;
use App\Livewire\Author\NovelEditor;
use App\Livewire\Author\ChapterEditor;
use App\Livewire\Author\Requests;
use App\Livewire\Catalog;
use App\Http\Middleware\CheckGeoRestriction;

Route::get('/storage/{path}', function (string $path) {
    $path = trim(str_replace(['../', '..\\'], '', $path), '/');
    if ($path === '') abort(404);
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || !is_file($fullPath)) abort(404);
    $mime = match(strtolower(pathinfo($fullPath, PATHINFO_EXTENSION))) {
        'webp' => 'image/webp', 'jpg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'gif' => 'image/gif', 'svg' => 'image/svg+xml', default => mime_content_type($fullPath) ?: 'application/octet-stream',
    };
    return response()->file($fullPath, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=2592000']);
})->where('path', '.+')->name('storage.serve');

Route::get('/', [NovelController::class, 'index'])->name('home');
Route::get('/updates', [NovelController::class, 'updates'])->name('updates');
Route::get('/novels', Catalog::class)->name('catalog');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/user/{id}', [UserController::class, 'show'])->name('users.show');
Route::get('/rankings', [RankingsController::class, 'index'])->name('rankings');
Route::get('/info/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/api-docs', fn() => view('api-docs'))->middleware(['auth', \App\Http\Middleware\RestrictApiDocs::class])->name('api.docs');

Route::get('/teams', fn() => view('teams.soon'))->name('teams.index');
Route::get('/teams/create', fn() => redirect()->route('teams.index'))->middleware('auth')->name('teams.create');
Route::post('/teams/create', fn() => redirect()->route('teams.index'))->middleware('auth')->name('teams.store');
Route::get('/teams/{slug}', fn() => redirect()->route('teams.index'))->name('teams.show');
Route::get('/teams/{slug}/manage', fn() => redirect()->route('teams.index'))->middleware('auth')->name('teams.manage');
Route::get('/invite/{token}', fn() => redirect()->route('teams.index'))->middleware('auth')->name('teams.invite.accept');

Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
Route::post('/subscription/plus/{plan}', [SubscriptionController::class, 'subscribePlus'])->middleware('auth')->name('subscription.plus');
Route::post('/subscription/{novel}', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
Route::post('/subscription/bundle/{author}', [SubscriptionController::class, 'subscribeBundle'])->name('subscription.bundle');
Route::get('/novel/subscription', fn() => redirect()->route('subscription.index'));

Route::middleware('guest')->group(function() {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/library', [\App\Http\Controllers\LibraryController::class, 'index'])->name('library');
    Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1')->name('verification.send');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::post('/profile/email-change/request', [\App\Http\Controllers\EmailChangeController::class, 'submit'])->middleware('throttle:5,10')->name('email-change.submit');
    Route::post('/profile/email-change/verify',  [\App\Http\Controllers\EmailChangeController::class, 'verify'])->name('email-change.verify');
    Route::post('/profile/email-change/cancel',  [\App\Http\Controllers\EmailChangeController::class, 'cancel'])->name('email-change.cancel');
    
    Route::get('/my-novels', \App\Livewire\Author\AuthorDashboard::class)->name('my-novels');
    Route::get('/author/requests', Requests::class)->name('author.requests');
    Route::get('/author/novel/create', NovelEditor::class)->name('author.novel.create');
    Route::get('/author/novel/{id}/edit', NovelEditor::class)->name('author.novel.edit');

    Route::get('/moderator/password', [\App\Http\Controllers\ModeratorUnlockController::class, 'show'])->name('moderator.password');
    Route::post('/moderator/unlock', [\App\Http\Controllers\ModeratorUnlockController::class, 'unlock'])->name('moderator.unlock');
    Route::post('/moderator/logout', [\App\Http\Controllers\ModeratorUnlockController::class, 'logout'])->name('moderator.logout');
    Route::get('/moderator', \App\Livewire\Moderator\ModeratorPanel::class)->middleware(\App\Http\Middleware\ModeratorAuth::class)->name('moderator.panel');
    Route::post('/api/upload/chapter-image', [\App\Http\Controllers\Api\ChapterImageController::class, 'upload'])->name('api.chapter.image.upload');
});

Route::middleware([CheckGeoRestriction::class])->group(function () {
    Route::get('/novel/{id}', [NovelController::class, 'show'])->where('id', '[0-9]+')->name('novel.show');
    Route::get('/novel/{novel_id}/chapter/{chapter_id}', [NovelController::class, 'read'])->where(['novel_id' => '[0-9]+', 'chapter_id' => '[0-9]+'])->name('novel.read');
    Route::get('/api/novel/{novel_id}/chapter/{chapter_id}/content', [NovelController::class, 'chapterContent'])->where(['novel_id' => '[0-9]+', 'chapter_id' => '[0-9]+'])->name('api.chapter.content');
    Route::get('/api/chapter/{id}/comments', [App\Http\Controllers\Api\ChapterApiController::class, 'comments'])->where('id', '[0-9]+')->name('api.chapter.comments');
    Route::post('/api/chapter/{id}/comments', [App\Http\Controllers\Api\ChapterApiController::class, 'storeComment'])->where('id', '[0-9]+')->name('api.chapter.comments.store');
    Route::post('/api/comments/{id}/like', [App\Http\Controllers\Api\ChapterApiController::class, 'toggleLike'])->where('id', '[0-9]+')->name('api.comments.like');
    Route::post('/api/chapter/{id}/error', [App\Http\Controllers\Api\ChapterApiController::class, 'reportError'])->where('id', '[0-9]+')->middleware('auth')->name('api.chapter.error');
    Route::post('/api/chapter/{id}/beta-note', [App\Http\Controllers\Api\ChapterApiController::class, 'storeBetaNote'])->where('id', '[0-9]+')->middleware('auth')->name('api.chapter.beta-note');
    Route::get('/api/chapter/{id}/bookmark', [App\Http\Controllers\Api\ChapterApiController::class, 'checkBookmark'])->where('id', '[0-9]+')->name('api.chapter.bookmark.check');
    Route::post('/api/chapter/{id}/bookmark', [App\Http\Controllers\Api\ChapterApiController::class, 'toggleBookmark'])->where('id', '[0-9]+')->middleware('auth')->name('api.chapter.bookmark.toggle');
    Route::post('/api/chapter/{id}/complaint', [App\Http\Controllers\Api\ChapterApiController::class, 'complaint'])->where('id', '[0-9]+')->middleware('auth')->name('api.chapter.complaint');
});
Route::get('/novel/{id}/download/{format}', [App\Http\Controllers\DownloadController::class, 'download'])->name('novel.download');
Route::post('/progress/save', [App\Http\Controllers\ProgressController::class, 'save'])->name('progress.save');
Route::get('/blog', \App\Livewire\BlogIndex::class)->name('blog.index');

Route::get('/reviews',            \App\Livewire\ReviewsIndex::class)->name('reviews.index');
Route::get('/reviews/create',     [\App\Http\Controllers\ReviewController::class, 'create'])->middleware('auth')->name('reviews.create');
Route::post('/reviews',           [\App\Http\Controllers\ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');
Route::get('/reviews/{review}',   [\App\Http\Controllers\ReviewController::class, 'show'])->name('reviews.show');
Route::get('/reviews/{review}/edit', [\App\Http\Controllers\ReviewController::class, 'edit'])->middleware('auth')->name('reviews.edit');
Route::patch('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'update'])->middleware('auth')->name('reviews.update');
Route::delete('/reviews/{review}',[\App\Http\Controllers\ReviewController::class, 'destroy'])->middleware('auth')->name('reviews.destroy');
Route::post('/reviews/{review}/recommend', [\App\Http\Controllers\ReviewController::class, 'recommend'])->middleware('auth')->name('reviews.recommend');
Route::post('/reviews/{review}/pin',       [\App\Http\Controllers\ReviewController::class, 'pin'])->middleware('auth')->name('reviews.pin');

Route::get('/recruitment', \App\Livewire\RecruitmentIndex::class)->name('recruitment.index');
Route::get('/recruitment/{slug}/apply', \App\Livewire\RecruitmentApply::class)->name('recruitment.apply');
Route::get('/author/stats', \App\Livewire\Author\Stats::class)->middleware('auth')->name('author.stats');
Route::get('/author/errors', \App\Livewire\Author\ChapterErrors::class)->middleware('auth')->name('author.errors');
Route::get('/author/novel/{id}/stats', \App\Livewire\Author\NovelStats::class)->middleware('auth')->name('author.novel.stats');
Route::get('/blog/{slug}', \App\Livewire\BlogShow::class)->name('blog.show');
Route::post('/toggle-theme', [App\Http\Controllers\ThemeController::class, 'toggle'])->name('theme.toggle');
Route::get('/messages', \App\Livewire\Chat::class)->middleware(['auth', \App\Http\Middleware\RestrictMessages::class])->name('messages');
Route::get('/messages/user/{user}', fn($user) => redirect()->route('messages', ['user' => $user]))->middleware(['auth', \App\Http\Middleware\RestrictMessages::class])->name('messages.user');
Route::get('/notifications', \App\Livewire\UserNotifications::class)->middleware('auth')->name('notifications');
Route::get('/author/novel/{id}/import', \App\Livewire\Author\ChapterImport::class)->middleware('auth')->name('author.novel.import');
Route::get('/author/novel/{novel}/chapter/create', \App\Livewire\Author\ChapterEditor::class)->middleware('auth')->name('author.chapter.create');
Route::get('/author/novel/{novel}/chapter/{chapter}/edit', \App\Livewire\Author\ChapterEditor::class)->middleware('auth')->name('author.chapter.edit');

Route::get('/guide', fn() => view('pages.guide'))->name('guide');
Route::get('/support',       \App\Livewire\Support\SupportIndex::class)->middleware('auth')->name('support.index');
Route::get('/support/{id}',  \App\Livewire\Support\SupportTicketShow::class)->middleware('auth')->where('id', '[0-9]+')->name('support.show');
