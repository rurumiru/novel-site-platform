<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use App\Models\Novel;
use App\Models\User;
use App\Models\AdBanner;
use App\Observers\NovelObserver;
use App\Observers\UserObserver;
use App\Observers\AdBannerObserver;
use App\Observers\ChapterObserver;
use App\Models\Chapter;
use Filament\Forms\Components\FileUpload;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useTailwind();

        \Carbon\Carbon::setLocale(config('app.locale', 'ru'));
        setlocale(LC_TIME, 'ru_RU.UTF-8', 'russian');

        if (!$this->app->runningInConsole() && request()->hasHeader('Host')) {
            $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
            $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;

            $host = $forwardedHost
                ? trim(explode(',', $forwardedHost)[0])
                : request()->getHost();

            $scheme = $forwardedProto
                ? trim(explode(',', $forwardedProto)[0])
                : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');

            $root = $scheme . '://' . $host;

            config(['filesystems.disks.public.url' => $root . '/storage']);
            URL::forceRootUrl($root);
            URL::forceScheme($scheme);
        }
        
        Novel::observe(NovelObserver::class);
        User::observe(UserObserver::class);
        AdBanner::observe(AdBannerObserver::class);
        Chapter::observe(ChapterObserver::class);

        try {
            if (class_exists(FileUpload::class)
                && method_exists(FileUpload::class, 'configureUsing')) {
                FileUpload::configureUsing(function (FileUpload $component): void {
                    if (method_exists($component, 'fetchFileInformation')) {
                        $component->fetchFileInformation(false);
                    }
                });
            }
        } catch (\Throwable $e) {
        }
    }
}
