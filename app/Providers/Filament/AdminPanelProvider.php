<?php
namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\SelectDatabaseByDomain;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('eriiba')
            ->favicon(asset('favicon.ico'))
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors([
                'primary' => [
                    50  => '#eff4ff',
                    100 => '#dbe7fe',
                    200 => '#bfd4fe',
                    300 => '#92b7fd',
                    400 => '#5d90fa',
                    500 => '#3a70f4',
                    600 => '#2459d6',
                    700 => '#1c47ad',
                    800 => '#1d3f88',
                    900 => '#1d386b',
                    950 => '#162447',
                ],
                'danger'  => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info'    => Color::Sky,
                'gray'    => Color::Slate,
            ])
            ->font('Manrope')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make('Контент')
                    ->icon('heroicon-o-book-open')
                    ->collapsible(),
                NavigationGroup::make('Пользователи')
                    ->icon('heroicon-o-users')
                    ->collapsible(),
                NavigationGroup::make('Финансы')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible(),
                NavigationGroup::make('Модерация')
                    ->icon('heroicon-o-shield-check')
                    ->collapsible(),
                NavigationGroup::make('Сообщество')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->collapsible(),
                NavigationGroup::make('Сайт')
                    ->icon('heroicon-o-globe-alt')
                    ->collapsible()
                    ->collapsed(),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<link rel="preconnect" href="https://fonts.googleapis.com">' .
                    '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' .
                    '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Source+Serif+Pro:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap">' .
                    '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">' .
                    '<link rel="stylesheet" href="' . asset('css/eriiba-admin-v3.css?v=' . \App\Models\Setting::retrieve('css_version', '5')) . '">'
                )
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): HtmlString => new HtmlString(
                    '<a href="' . url('/') . '" target="_blank" class="eri-admin-site-link">' .
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3h7v7M10 14L21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>' .
                    '<span>Открыть сайт</span></a>'
                )
            )
            ->middleware([
                SelectDatabaseByDomain::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
