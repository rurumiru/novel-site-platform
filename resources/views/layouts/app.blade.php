<!DOCTYPE html>
<html lang="ru" class="scroll-smooth no-reader" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="yandex" content="none">
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::retrieve('site_description', 'Библиотека новелл — читайте лучшие произведения'))">
    <title>@yield('title', \App\Models\Setting::retrieve('site_name', 'eriiba'))</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Source+Serif+Pro:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        /* Защита от тофу: пока FA не загружен, скрываем содержимое иконок */
        .fa, .fas, .far, .fab, .fal, .fad, .fass,
        [class^="fa-"], [class*=" fa-"] { font-display: block; }
    </style>

    <link rel="stylesheet" href="{{ asset('css/eriiba.css') }}?v={{ \App\Models\Setting::retrieve('css_version', '5') }}">
    <link rel="stylesheet" href="{{ asset('css/eriiba-pages.css') }}?v={{ \App\Models\Setting::retrieve('css_version', '5') }}">
    <link rel="stylesheet" href="{{ asset('css/reviews.css') }}?v={{ \App\Models\Setting::retrieve('css_version', '5') }}">

    <script>
        (function(){var w=console.warn;console.warn=function(){if(arguments[0]&&String(arguments[0]).includes('cdn.tailwindcss.com'))return;w.apply(console,arguments);};})();
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=typography,forms,aspect-ratio"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', 'Inter', 'sans-serif'],
                        serif: ['"Source Serif Pro"', 'Merriweather', 'serif'],
                        display: ['Fraunces', 'serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        slate: { 850: '#1e2028', 900: '#16181d', 950: '#0e0f13' },
                        // accent теперь синий, как в дизайне Eriiba
                        accent: { DEFAULT: '#2f6df0', light: '#5b8def', dark: '#2459d6' },
                        eriiba: { DEFAULT: '#2f6df0', light: '#5b8def', dark: '#2459d6' }
                    }
                }
            }
        }
    </script>

    <script>
        (function() {
            function applyTheme() {
                var t = localStorage.getItem('theme') || 'light';
                // sepia больше НЕ глобальная тема — это «бумага» только для читалки.
                // Если в LS залип старый sepia — мигрируем в light, чтобы не «утекало».
                if (t === 'sepia') {
                    t = 'light';
                    localStorage.setItem('theme', 'light');
                    localStorage.setItem('reader_paper', 'sepia');
                }
                if (!['light','dark'].includes(t)) t = 'light';
                document.documentElement.setAttribute('data-theme', t);
                // Tailwind dark: класс — синхронизирован с data-theme=dark
                document.documentElement.classList.toggle('dark', t === 'dark');
            }
            applyTheme();

            new MutationObserver(function() {
                var t = localStorage.getItem('theme') || 'light';
                if (document.documentElement.getAttribute('data-theme') !== t) {
                    applyTheme();
                }
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });

            document.addEventListener('livewire:navigate', applyTheme);
            document.addEventListener('livewire:navigated', function() {
                applyTheme();
                requestAnimationFrame(applyTheme);
                document.documentElement.classList.add('loaded');

                var isReader = !!document.getElementById('reader-scroll-area');
                if (!isReader) {
                    document.documentElement.classList.add('no-reader');
                    document.documentElement.classList.remove('h-full');
                    document.documentElement.style.removeProperty('height');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('height');
                    document.body.style.overflow = 'visible';
                    document.body.style.height = 'auto';
                    window.scrollTo(0, 0);
                } else {
                    document.documentElement.classList.remove('no-reader');
                    document.documentElement.classList.add('h-full');
                }
            });
            document.addEventListener('pageshow', applyTheme);
            if (document.readyState === 'complete') {
                document.documentElement.classList.add('loaded');
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    document.documentElement.classList.add('loaded');
                });
            }
        })();
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { -webkit-tap-highlight-color: transparent; }
        html:not(.loaded) body{ opacity: 0; }
        html.loaded body{ opacity: 1; transition: opacity 0.2s ease-out; }
        .page-transition { view-transition-name: main; }
        .zoomable { cursor: zoom-in; }
        figcaption, .attachment__caption, .attachment__name, .attachment__size,
        .trix-content figcaption, .fi-fo-rich-editor figcaption { display: none !important; }
        html, body { scrollbar-width: thin; }
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 6px; border: 2px solid var(--bg); }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-faint); }
        html.no-reader body { overflow: auto !important; height: auto !important; min-height: 100vh; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 0px); }
        /* Старый bottom-nav убран — нижний padding больше не нужен */
        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
        }
    </style>
    @filamentStyles
    @livewireStyles
</head>
<body class="flex flex-col min-h-screen antialiased" x-data="themeData()">

    @php
        $deskLinks = [
            ['route'=>'home', 'label'=>'Главная', 'match'=>'home'],
            [
                'label' => 'Читать',
                'match' => 'catalog|updates|rankings',
                'icon'  => 'fa-solid fa-book-open',
                'children' => [
                    ['route'=>'catalog',  'label'=>'Каталог',     'match'=>'catalog',  'icon'=>'fa-solid fa-magnifying-glass', 'desc'=>'Все новеллы и ранобэ'],
                    ['route'=>'updates',  'label'=>'Обновления',  'match'=>'updates',  'icon'=>'fa-solid fa-clock-rotate-left','desc'=>'Свежие главы'],
                    ['route'=>'rankings', 'label'=>'Рейтинги',    'match'=>'rankings', 'icon'=>'fa-solid fa-trophy',           'desc'=>'Топ по популярности'],
                ],
            ],
            [
                'label' => 'Сообщество',
                'match' => 'blog.*|recruitment.*|users.*|reviews.*',
                'icon'  => 'fa-solid fa-users',
                'children' => [
                    ['route'=>'reviews.index',    'label'=>'Обзоры',  'match'=>'reviews.*',    'icon'=>'fa-solid fa-star-half-stroke', 'desc'=>'Рецензии и продвижение работ'],
                    ['route'=>'blog.index',       'label'=>'Блог',    'match'=>'blog.*',       'icon'=>'fa-solid fa-newspaper',   'desc'=>'Статьи и новости'],
                    ['route'=>'users.index',      'label'=>'Люди',    'match'=>'users.*',      'icon'=>'fa-solid fa-user-group',  'desc'=>'Читатели и авторы'],
                    ['route'=>'recruitment.index','label'=>'Набор',   'match'=>'recruitment.*','icon'=>'fa-solid fa-bullhorn',    'desc'=>'Поиск соавторов'],
                ],
            ],
        ];

        $deskLinks = collect($deskLinks)->map(function ($lnk) {
            if (!empty($lnk['children'])) {
                $lnk['children'] = collect($lnk['children'])
                    ->filter(fn($c) => \Illuminate\Support\Facades\Route::has($c['route']))
                    ->values()
                    ->all();
            }
            return $lnk;
        })->filter(function ($lnk) {
            if (!empty($lnk['children'])) return count($lnk['children']) > 0;
            return !empty($lnk['route']) && \Illuminate\Support\Facades\Route::has($lnk['route']);
        })->values()->all();

        $siteName = \App\Models\Setting::retrieve('site_name', 'eriiba');
    @endphp

    @hasSection('hideNav')
    @else
    <nav class="eri-nav" x-data="{ drawerOpen: false, searchOpen: false }">
        <div class="eri-nav-inner">

            <button type="button" class="eri-icon-btn eri-burger" @click="drawerOpen = true" aria-label="Меню">
                <i class="fa-solid fa-bars"></i>
            </button>

            <a href="{{ route('home') }}" wire:navigate class="eri-logo">
                <span class="eri-logo-text">{{ $siteName }}</span>
            </a>

            <div class="eri-nav-links eri-nav-links-desktop">
                @foreach($deskLinks as $lnk)
                    @php
                        $hasChildren = !empty($lnk['children']);
                        $act = $hasChildren
                            ? collect(explode('|', $lnk['match']))->some(fn($m) => request()->routeIs($m))
                            : request()->routeIs($lnk['match']);
                    @endphp
                    @if($hasChildren)
                        @php static $dropIdx = 0; $dropId = 'drop_' . $dropIdx++; @endphp
                        <div class="eri-nav-dropdown"
                             x-data="{ open: false, id: '{{ $dropId }}' }"
                             @mouseenter="open = true"
                             @mouseleave="open = false"
                             @nav-close.window="if ($event.detail.except !== id) open = false">
                            <button type="button"
                                    @click="open = !open; if(open) $dispatch('nav-close', { except: id })"
                                    class="eri-nav-drop-btn {{ $act ? 'active' : '' }}"
                                    :class="open ? 'open' : ''">
                                {{ $lnk['label'] }}
                                <i class="fa-solid fa-chevron-down eri-nav-drop-caret" :class="open ? 'rotated' : ''"></i>
                            </button>
                            <div class="eri-nav-drop-menu" :class="open ? 'visible' : ''">
                                <div class="eri-nav-drop-menu-inner">
                                @foreach($lnk['children'] as $child)
                                    @php $childAct = request()->routeIs($child['match']); @endphp
                                    <a href="{{ route($child['route']) }}" wire:navigate
                                       class="eri-nav-drop-item {{ $childAct ? 'active' : '' }}"
                                       @click="open = false">
                                        <div class="eri-nav-drop-icon">
                                            <i class="{{ $child['icon'] ?? 'fa-solid fa-circle' }}"></i>
                                        </div>
                                        <div>
                                            <div class="eri-nav-drop-label">{{ $child['label'] }}</div>
                                            @if(!empty($child['desc']))
                                                <div class="eri-nav-drop-desc">{{ $child['desc'] }}</div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route($lnk['route']) }}" wire:navigate class="{{ $act ? 'active' : '' }}">{{ $lnk['label'] }}</a>
                    @endif
                @endforeach
                @auth
                    <a href="{{ route('library') }}" wire:navigate class="{{ request()->routeIs('library') ? 'active' : '' }}" title="Моя библиотека">
                        <i class="fa-solid fa-bookmark" style="font-size:11px;margin-right:4px;"></i>Библиотека
                    </a>
                    @if(Auth::user()->hasRole(['author', 'super_admin', 'owner']))
                        <a href="{{ route('my-novels') }}" wire:navigate class="{{ request()->routeIs('my-novels') ? 'active' : '' }}">Мои новеллы</a>
                    @endif
                @endauth
            </div>

            <div class="eri-nav-right">
                
                <form action="{{ route('catalog') }}" method="get" class="eri-search eri-search-desktop">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="search" placeholder="Поиск новелл, авторов, тегов" autocomplete="off">
                    <span class="kbd">⌘ K</span>
                </form>
                <button type="button" class="eri-icon-btn eri-search-mobile-btn" @click="searchOpen = true" aria-label="Поиск">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <div class="eri-theme-toggle eri-hide-mobile" role="tablist" aria-label="Тема">
                    <button type="button" :class="theme === 'light' ? 'on' : ''" @click="setTheme('light')" title="Светлая">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                    </button>
                    <button type="button" :class="theme === 'dark' ? 'on' : ''" @click="setTheme('dark')" title="Тёмная">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                </div>
                <button type="button" class="eri-icon-btn eri-show-mobile" @click="toggleTheme()" title="Тема">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>

                @auth
                    @if(Auth::user()->hasRole(['owner', 'super_admin', 'deputy_admin', 'moderator', 'editor', 'author']))
                        <span class="eri-hide-mobile">@livewire('messages-icon')</span>
                    @endif
                    @php $deskNotifCount = Auth::user()->unreadNotifications()->count(); @endphp
                    <a href="{{ route('notifications') }}" wire:navigate class="eri-icon-btn" title="Уведомления">
                        <i class="fa-solid fa-bell"></i>
                        @if($deskNotifCount > 0)
                            <span class="badge-num">{{ $deskNotifCount > 9 ? '9+' : $deskNotifCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile') }}" wire:navigate class="eri-avatar" title="{{ Auth::user()->name }}">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" loading="lazy">
                        @else
                            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 2)) }}
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="eri-btn primary sm">Войти</a>
                    <a href="{{ route('register') }}" wire:navigate class="eri-btn sm eri-hide-mobile">Регистрация</a>
                @endauth
            </div>
        </div>

        <template x-teleport="body">
        <div x-show="drawerOpen" x-cloak class="eri-drawer-overlay" @click.self="drawerOpen = false">
            <aside class="eri-drawer" x-show="drawerOpen"
                   x-transition:enter="eri-drawer-enter"
                   x-transition:enter-start="eri-drawer-enter-start"
                   x-transition:enter-end="eri-drawer-enter-end"
                   x-transition:leave="eri-drawer-leave"
                   x-transition:leave-start="eri-drawer-enter-end"
                   x-transition:leave-end="eri-drawer-enter-start"
                   @click.stop>
                <div class="eri-drawer-head">
                    <a href="{{ route('home') }}" wire:navigate class="eri-logo" @click="drawerOpen = false">
                        <span>{{ $siteName }}</span>
                    </a>
                    <button type="button" class="eri-icon-btn" @click="drawerOpen = false" aria-label="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                @auth
                    <a href="{{ route('profile') }}" wire:navigate class="eri-drawer-user" @click="drawerOpen = false">
                        <span class="eri-avatar" style="width:44px;height:44px;font-size:14px;flex:none;">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="">
                            @else
                                {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 2)) }}
                            @endif
                        </span>
                        <div style="min-width:0;">
                            <div style="font-weight:600;color:var(--text);font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name }}</div>
                            <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">{{ Auth::user()->username ? '@'.Auth::user()->username : 'id'.Auth::user()->id }}</div>
                        </div>
                    </a>
                @else
                    <div class="eri-drawer-auth">
                        <a href="{{ route('login') }}" wire:navigate class="eri-btn primary block" @click="drawerOpen = false">Войти</a>
                        <a href="{{ route('register') }}" wire:navigate class="eri-btn block" @click="drawerOpen = false">Регистрация</a>
                    </div>
                @endauth

                <div class="eri-drawer-section-title">Навигация</div>
                <nav class="eri-drawer-nav">
                    @foreach($deskLinks as $lnk)
                        @if(!empty($lnk['children']))
                            
                            <div class="eri-drawer-group-label">{{ $lnk['label'] }}</div>
                            @foreach($lnk['children'] as $child)
                                @php $childAct = request()->routeIs($child['match']); @endphp
                                <a href="{{ route($child['route']) }}" wire:navigate
                                   class="{{ $childAct ? 'active' : '' }}"
                                   @click="drawerOpen = false">
                                    <i class="{{ $child['icon'] ?? 'fa-solid fa-circle' }}" style="font-size:13px;"></i>
                                    <span>{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        @else
                            @php
                                $act = request()->routeIs($lnk['match']);
                                $iconMap = ['home' => 'fa-house'];
                                $ic = 'fa-solid ' . ($iconMap[$lnk['route']] ?? 'fa-circle');
                            @endphp
                            <a href="{{ route($lnk['route']) }}" wire:navigate
                               class="{{ $act ? 'active' : '' }}"
                               @click="drawerOpen = false">
                                <i class="{{ $ic }}"></i>
                                <span>{{ $lnk['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                    @auth
                        @if(Auth::user()->hasRole(['author', 'super_admin', 'owner']))
                            <a href="{{ route('my-novels') }}" wire:navigate class="{{ request()->routeIs('my-novels*') ? 'active' : '' }}" @click="drawerOpen = false">
                                <i class="fa-solid fa-feather"></i><span>Мои новеллы</span>
                            </a>
                        @endif
                        <a href="{{ route('library') }}" wire:navigate class="{{ request()->routeIs('library') ? 'active' : '' }}" @click="drawerOpen = false">
                            <i class="fa-solid fa-bookmark"></i><span>Моя библиотека</span>
                        </a>
                    @endauth
                </nav>

                @auth
                    <div class="eri-drawer-section-title">Личный кабинет</div>
                    <nav class="eri-drawer-nav">
                        
                        @if(Auth::user()->hasRole(['owner', 'super_admin', 'deputy_admin', 'moderator', 'editor', 'author']))
                            <a href="{{ route('messages') }}" wire:navigate @click="drawerOpen = false">
                                <i class="fa-regular fa-comment-dots"></i><span>Сообщения</span>
                            </a>
                        @endif
                        @if(Auth::user()->hasRole(['owner', 'super_admin', 'deputy_admin', 'moderator']))
                            <a href="{{ route('moderator.panel') }}" wire:navigate @click="drawerOpen = false">
                                <i class="fa-solid fa-shield-halved"></i><span>Модерация</span>
                            </a>
                        @endif
                    </nav>
                @endauth

                <div class="eri-drawer-section-title">Тема</div>
                <div class="eri-drawer-themes">
                    <button type="button" :class="theme === 'light' ? 'on' : ''" @click="setTheme('light')">
                        <i class="fa-solid fa-sun"></i> Светлая
                    </button>
                    <button type="button" :class="theme === 'dark' ? 'on' : ''" @click="setTheme('dark')">
                        <i class="fa-solid fa-moon"></i> Тёмная
                    </button>
                </div>

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="eri-drawer-logout">
                        @csrf
                        <button type="submit" class="eri-btn block" style="color:var(--err); border-color:var(--err);">
                            <i class="fa-solid fa-right-from-bracket"></i> Выйти
                        </button>
                    </form>
                @endauth
            </aside>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="searchOpen" x-cloak class="eri-search-overlay" @click.self="searchOpen = false"
             x-init="$watch('searchOpen', v => v && $nextTick(() => document.getElementById('mob-search-input')?.focus()))">
            <form action="{{ route('catalog') }}" method="get" class="eri-search-overlay-form" @click.stop>
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="mob-search-input" type="search" name="search" placeholder="Поиск новелл, авторов, тегов…" autocomplete="off">
                <button type="button" class="eri-icon-btn" @click="searchOpen = false" aria-label="Закрыть">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </form>
        </div>
        </template>
    </nav>
    @endif

    @php
        $mobHome    = request()->routeIs('home');
        $mobCat     = request()->routeIs('catalog');
        $mobLib     = request()->routeIs('library') || request()->routeIs('updates');
        $mobBlog    = request()->routeIs('blog.*') || request()->routeIs('rankings');
        $mobProf    = request()->routeIs('profile') || request()->routeIs('users.*');
        $isReaderPg = request()->routeIs('novel.read');
    @endphp
    @unless($isReaderPg)
    <nav class="eri-bottom-nav">
        <div class="eri-bottom-nav-row">
            <a href="{{ route('home') }}" wire:navigate class="{{ $mobHome ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i>
                <span>Главная</span>
            </a>
            <a href="{{ route('blog.index') }}" wire:navigate class="{{ $mobBlog ? 'active' : '' }}">
                <i class="fa-solid fa-newspaper"></i>
                <span>Блог</span>
            </a>
            <a href="{{ route('catalog') }}" wire:navigate class="center {{ $mobCat ? 'active' : '' }}">
                <span class="dot"><i class="fa-solid fa-compass"></i></span>
                <span>Каталог</span>
            </a>
            @auth
                <a href="{{ route('library') }}" wire:navigate class="{{ $mobLib ? 'active' : '' }}">
                    <i class="fa-solid fa-bookmark"></i>
                    <span>Библиотека</span>
                </a>
                <a href="{{ route('profile') }}" wire:navigate class="{{ $mobProf ? 'active' : '' }}">
                    @if(Auth::user()->avatar_url)
                        <img src="{{ Auth::user()->avatar_url }}" alt="" style="width:18px;height:18px;border-radius:50%;object-fit:cover;">
                    @else
                        <i class="fa-solid fa-user"></i>
                    @endif
                    <span>Профиль</span>
                </a>
            @else
                <a href="{{ route('updates') }}" wire:navigate class="{{ request()->routeIs('updates') ? 'active' : '' }}">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Обновления</span>
                </a>
                <a href="{{ route('login') }}" wire:navigate>
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Войти</span>
                </a>
            @endauth
        </div>
    </nav>
    @endunless

    <main class="flex-grow">
        @if(session('error'))
            <div class="eri-section" style="padding-top:14px;padding-bottom:0;">
                <div class="eri-alert err">{{ session('error') }}</div>
            </div>
        @endif
        @if(session('success'))
            <div class="eri-section" style="padding-top:14px;padding-bottom:0;">
                <div class="eri-alert ok">{{ session('success') }}</div>
            </div>
        @endif
        @if(isset($slot)) {{ $slot }} @else @yield('content') @endif
    </main>

    @hasSection('hideFooter')
    @else
        @include('partials.eriiba-footer')
    @endif

    <script>
    function themeData() {
        return {
            theme: localStorage.getItem('theme') || 'light',
            init() {
                this.theme = localStorage.getItem('theme') || 'light';
            },
            setTheme(next) {
                if (!['light','dark','sepia'].includes(next)) return;
                this.theme = next;
                localStorage.setItem('theme', next);
                document.documentElement.setAttribute('data-theme', next);
                document.documentElement.classList.toggle('dark', next === 'dark');
                fetch('{{ route("theme.toggle") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    body: JSON.stringify({ theme: next })
                }).catch(() => {});
            },
            toggleTheme() {
                this.setTheme(this.theme === 'dark' ? 'light' : 'dark');
            }
        };
    }
    </script>
    @filamentScripts
    @stack('scripts')
    @livewireScripts
    <script>document.addEventListener('DOMContentLoaded', () => { const initImages = () => { const images = document.querySelectorAll('.prose img, .glass-card img, .group-image img, .reader-body img, .im-body img'); images.forEach((img, i) => { if (!img.loading && i > 2) img.loading = 'lazy'; if (img.classList.contains('no-zoom') || img.width < 50 || img.closest('.fi-avatar') || img.closest('.eri-avatar') || img.closest('.eri-cover')) return; img.classList.add('zoomable'); const parentLink = img.closest('a'); if (parentLink && !parentLink.href.match(/\.(jpeg|jpg|gif|png|webp)$/i)) return; img.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); window.dispatchEvent(new CustomEvent('open-lightbox', { detail: img.src })); }); }); }; initImages(); document.addEventListener('livewire:navigated', initImages); document.addEventListener('livewire:updated', initImages); });</script>
</body>
</html>
