<?php

namespace App\Forum;

use App\Forum\Models\Post;
use App\Forum\Models\Report;
use App\Forum\Models\Section;
use App\Forum\Models\Thread;
use App\Forum\Policies\PostPolicy;
use App\Forum\Policies\ReportPolicy;
use App\Forum\Policies\ThreadPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ForumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Services\FeedService::class);
        $this->app->singleton(Services\AccessService::class);
        $this->app->singleton(Services\LogService::class);
        $this->app->singleton(Services\NotificationService::class);
        $this->app->singleton(Services\ThreadService::class);
        $this->app->singleton(Services\PostService::class);
        $this->app->singleton(Services\ReactionService::class);
        $this->app->singleton(Services\ReportService::class);
        $this->app->singleton(Services\SubscriptionService::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerPolicies();
        $this->registerViews();

        Route::bind('thread', function ($value) {
            return Thread::where('slug', $value)->firstOrFail();
        });
    }

    private function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/routes.php');
    }

    private function registerPolicies(): void
    {
        Gate::policy(Thread::class, ThreadPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
    }

    private function registerViews(): void
    {
        $viewPath = resource_path('views/forum');
        if (is_dir($viewPath)) {
            $this->loadViewsFrom($viewPath, 'forum');
        }
    }
}
