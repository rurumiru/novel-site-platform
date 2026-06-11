@extends('layouts.app')
@section('meta_description', \App\Models\Setting::retrieve('site_description', 'Библиотека новелл — читайте лучшие произведения'))

@section('content')
<div class="eri-page">

    @php
        $services = config('homepage_services', [
            ['label' => 'Рейтинги',  'icon' => 'fa-trophy',           'color' => 'pink',   'route' => 'rankings'],
            ['label' => 'Обновления','icon' => 'fa-clock-rotate-left','color' => 'blue',   'route' => 'updates'],
            ['label' => 'Отзывы',    'icon' => 'fa-star-half-stroke', 'color' => 'green',  'route' => 'reviews.index'],
            ['label' => 'Каталог',   'icon' => 'fa-compass',          'color' => 'purple', 'route' => 'catalog'],
            ['label' => 'Блог',      'icon' => 'fa-newspaper',        'color' => 'gold',   'route' => 'blog.index'],
            ['label' => 'Команды',   'icon' => 'fa-users',            'color' => '',       'route' => 'teams.index'],
        ]);
        $services = collect($services)->filter(function ($svc) {
            return !empty($svc['route']) && \Illuminate\Support\Facades\Route::has($svc['route']);
        })->values()->all();
    @endphp
    @if(!empty($services))
        <section class="eri-section" style="padding-top:14px;padding-bottom:0;">
            <div class="home-services">
                @foreach($services as $svc)
                    <a href="{{ route($svc['route']) }}" wire:navigate class="home-service-item">
                        <span class="home-service-icon {{ $svc['color'] ?? '' }}">
                            <i class="fa-solid {{ $svc['icon'] }}"></i>
                        </span>
                        <span class="home-service-label">{{ $svc['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $isGuest_W   = !\Illuminate\Support\Facades\Auth::check();
        $hideAdult_W = $isGuest_W && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
        $isRu_W      = \App\Services\GeoService::isRu(request()->ip());

        $heroTopAllTime = \App\Models\Novel::where('is_published', true)
            ->when($isRu_W,      fn($q) => $q->where('is_restricted', false))
            ->when($hideAdult_W, fn($q) => $q->where('is_adult', false))
            ->when($isGuest_W,   fn($q) => $q->where('hide_from_guests', false))
            ->with('publisher:id,name')
            ->orderByDesc('views')
            ->take(7)->get();
    @endphp
    @if($heroTopAllTime->isNotEmpty())
        <section class="eri-section hero-section">
            <div class="hero-popular">
                <div class="hero-popular-head">
                    <h2 class="hero-popular-title">Популярное за всё время</h2>
                    <a href="{{ route('rankings') }}" wire:navigate class="hero-popular-more">Все рейтинги →</a>
                </div>
                <div class="hero-popular-row">
                    @foreach($heroTopAllTime as $n)
                        <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="hero-popular-card">
                            <div class="hero-popular-cover">
                                <x-eriiba.cover :novel="$n" />
                            </div>
                            <div class="hero-popular-name">{{ $n->title }}</div>
                            <div class="hero-popular-author">{{ $n->publisher?->name ?? '—' }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @php
        $adBanners = \App\Models\AdBanner::where('is_active', true)->orderBy('sort_order')->get();
        $adIndex = 0;
    @endphp

    @foreach($blocks as $block)
        @php $type = $block['type'] ?? 'block'; @endphp
        @if($type === 'continue' && !empty($block['data']) && $block['data']->count())
            <section class="eri-section continue-section">
                <x-eriiba.section-head :title="$block['title'] ?? 'Продолжить чтение'" :sub="$block['data']->count() . ' в работе'" />
                <div class="continue-grid">
                    @foreach($block['data']->take(6) as $progress)
                        @if($progress->novel)
                            @php
                                $pct = max(0, min(100, (int) ($progress->percent ?? 0)));
                            @endphp
                            <a href="{{ route('novel.read', [$progress->novel->id, $progress->chapter_id]) }}" class="continue-card">
                                <div class="continue-cover">
                                    <x-eriiba.cover :novel="$progress->novel" />
                                </div>
                                <div>
                                    <div class="continue-title">{{ $progress->novel->title }}</div>
                                    <div class="continue-chapter">{{ $progress->chapter?->title ?? 'Глава' }}</div>
                                    <div class="continue-bar"><div class="continue-bar-fill" style="width: {{ $pct }}%"></div></div>
                                    <div class="continue-stats"><span>{{ $pct }}% прочитано</span></div>
                                </div>
                                <div class="continue-cta"><i class="fa-solid fa-arrow-right"></i></div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @php
        $genres = \App\Models\Genre::withCount('novels')->orderByDesc('novels_count')->take(8)->get();
        $genreMeta = [
            'фэнтези'        => ['fantasy', 'fa-hat-wizard',         'Магия и волшебные миры'],
            'fantasy'        => ['fantasy', 'fa-hat-wizard',         'Магия и волшебные миры'],
            'литрпг'         => ['litrpg',  'fa-dice-d20',           'Игровые системы и подземелья'],
            'litrpg'         => ['litrpg',  'fa-dice-d20',           'Игровые системы и подземелья'],
            'роман'          => ['romance', 'fa-heart',              'О любви и чувствах'],
            'романтика'      => ['romance', 'fa-heart',              'О любви и чувствах'],
            'romance'        => ['romance', 'fa-heart',              'О любви и чувствах'],
            'экшен'          => ['action',  'fa-bolt',               'Битвы и приключения'],
            'боевик'         => ['action',  'fa-bolt',               'Битвы и приключения'],
            'action'         => ['action',  'fa-bolt',               'Битвы и приключения'],
            'sci-fi'         => ['scifi',   'fa-rocket',             'Будущее, космос, технологии'],
            'фантастика'     => ['scifi',   'fa-rocket',             'Будущее, космос, технологии'],
            'киберпанк'      => ['scifi',   'fa-microchip',          'Хай-тек и киберпространство'],
            'мистика'        => ['mystery', 'fa-eye',                'Загадки и тайны'],
            'mystery'        => ['mystery', 'fa-eye',                'Загадки и тайны'],
            'детектив'       => ['mystery', 'fa-magnifying-glass',   'Расследования и улики'],
            'триллер'        => ['mystery', 'fa-mask',               'Напряжение и опасность'],
            'thriller'       => ['mystery', 'fa-mask',               'Напряжение и опасность'],
            'повседневность' => ['slice',   'fa-mug-hot',            'Жизнь и обычные истории'],
            'slice'          => ['slice',   'fa-mug-hot',            'Жизнь и обычные истории'],
            'хоррор'         => ['horror',  'fa-ghost',              'Страхи, кошмары, паранойя'],
            'ужасы'          => ['horror',  'fa-ghost',              'Страхи, кошмары, паранойя'],
            'horror'         => ['horror',  'fa-ghost',              'Страхи, кошмары, паранойя'],
            'комедия'        => ['slice',   'fa-face-laugh-beam',    'Юмор и абсурд'],
            'юмор'           => ['slice',   'fa-face-laugh-beam',    'Юмор и абсурд'],
            'драма'          => ['romance', 'fa-masks-theater',      'Сильные эмоции и характеры'],
            'история'        => ['action',  'fa-scroll',             'Прошлое и эпохи'],
            'history'        => ['action',  'fa-scroll',             'Прошлое и эпохи'],
            'психология'     => ['mystery', 'fa-brain',              'Внутренний мир героев'],
            'постапокалипсис'=> ['horror',  'fa-radiation',          'Мир после катастрофы'],
            'стимпанк'       => ['action',  'fa-gears',              'Пар, шестерни и латунь'],
            'школа'          => ['slice',   'fa-graduation-cap',     'Учебные истории'],
            'академия'       => ['fantasy', 'fa-graduation-cap',     'Магические академии'],
            'боевые искусства' => ['action','fa-hand-fist',          'Сражения и техники'],
            'приключения'    => ['action',  'fa-compass',            'Путешествия и квесты'],
            'попаданец'      => ['fantasy', 'fa-person-walking-arrow-right', 'Перенос в другой мир'],
            'гарем'          => ['romance', 'fa-people-group',       'Истории с несколькими героинями'],
            'юри'            => ['romance', 'fa-venus-double',       'Романтика между девушками'],
            'яой'            => ['romance', 'fa-mars-double',        'Романтика между парнями'],
            'этти'           => ['romance', 'fa-fire',               'Чувственность 16+'],
            'хентай'         => ['romance', 'fa-fire-flame-curved',  'Эротика 18+'],
            'игры'           => ['litrpg',  'fa-gamepad',            'Виртуальные миры'],
            'спорт'          => ['action',  'fa-medal',              'Спортивные истории'],
            'кулинар'        => ['slice',   'fa-utensils',           'Готовка и еда'],
            'музыка'         => ['slice',   'fa-music',              'Музыкальные истории'],
            'военн'          => ['action',  'fa-shield-halved',      'Война и сражения'],
            'политика'       => ['mystery', 'fa-landmark',           'Власть и интриги'],
            'путешествия'    => ['action',  'fa-plane',              'Дороги и страны'],
            'магия'          => ['fantasy', 'fa-wand-magic-sparkles','Заклинания и волшебство'],
            'некромант'      => ['horror',  'fa-skull',              'Магия смерти'],
            'дракон'         => ['fantasy', 'fa-dragon',             'Драконы и крылатые'],
        ];
        $resolveGenre = function($name) use ($genreMeta) {
            $low = mb_strtolower($name);
            foreach ($genreMeta as $needle => $meta) {
                if (mb_strpos($low, $needle) !== false) return $meta;
            }
            return [null, 'fa-book-open', null];
        };
    @endphp
    @if($genres->count())
        <section class="eri-section">
            <x-eriiba.section-head title="Жанры" sub="найдите своё" :more="route('catalog')" moreLabel="Все жанры →" />
            <div class="genre-nav">
                @foreach($genres as $g)
                    @php [$variant, $icon, $desc] = $resolveGenre($g->name); @endphp
                    <a href="{{ route('catalog', ['genre' => $g->id]) }}" wire:navigate class="genre-tile {{ $variant }}">
                        <span class="genre-tile-icon"><i class="fa-solid {{ $icon }}"></i></span>
                        <span class="genre-tile-label">{{ $g->name }}</span>
                        <span class="genre-tile-count">{{ number_format($g->novels_count, 0, '.', ' ') }}</span>
                        @if($desc)<span class="genre-tile-desc">{{ $desc }}</span>@endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $isGuest_C  = !\Illuminate\Support\Facades\Auth::check();
        $hideAdult_C = $isGuest_C && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
        $isRu_C = \App\Services\GeoService::isRu(request()->ip());

        $baseQ = fn() => \App\Models\Novel::where('is_published', true)
            ->when($isRu_C,    fn($q) => $q->where('is_restricted', false))
            ->when($hideAdult_C, fn($q) => $q->where('is_adult', false))
            ->when($isGuest_C, fn($q) => $q->where('hide_from_guests', false))
            ->with(['genres'])
            ->withCount('chapters');

        $chartsPopular = $baseQ()
            ->withCount(['viewsLog as views_30d' => fn($q) => $q->where('viewed_at', '>=', now()->subDays(30)->toDateString())])
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
            ->orderByDesc('views_30d')->take(8)->get();

        $chartsRising = $baseQ()
            ->withCount(['viewsLog as views_7d' => fn($q) => $q->where('viewed_at', '>=', now()->subDays(7)->toDateString())])
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
            ->whereHas('viewsLog', fn($q) => $q->where('viewed_at', '>=', now()->subDays(7)->toDateString()))
            ->orderByDesc('views_7d')->take(8)->get();

        $chartsNew = $baseQ()
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
            ->latest()->take(8)->get();

        $chartsCompleted = $baseQ()
            ->where('status', 'completed')
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
            ->orderByDesc('avg_rating')->take(8)->get();

        $chartTabs = [
            'popular'   => ['Популярное',   'по подписчикам',   $chartsPopular,   'обновляется каждый понедельник'],
            'rising'    => ['Восходящие',   'всё ещё растёт',   $chartsRising,    'за последние 7 дней'],
            'new'       => ['Новые',        'свежие истории',   $chartsNew,       'за последние недели'],
            'completed' => ['Завершённые',  'по рейтингу',      $chartsCompleted, 'можно проглотить целиком'],
        ];

        $genreChipMap = [
            'фэнтези' => 'fantasy', 'fantasy' => 'fantasy', 'литрпг' => 'litrpg', 'litrpg' => 'litrpg',
            'роман' => 'romance', 'романтика' => 'romance', 'romance' => 'romance',
            'экшен' => 'action', 'боевик' => 'action', 'action' => 'action',
            'sci-fi' => 'scifi', 'фантастика' => 'scifi',
            'мистика' => 'mystery', 'mystery' => 'mystery', 'детектив' => 'mystery', 'триллер' => 'mystery',
            'повседневность' => 'slice', 'slice' => 'slice', 'комедия' => 'slice',
            'хоррор' => 'horror', 'ужасы' => 'horror', 'horror' => 'horror',
            'драма' => 'romance',
        ];
        $resolveChip = function($name) use ($genreChipMap) {
            $low = mb_strtolower($name);
            foreach ($genreChipMap as $needle => $cls) {
                if (mb_strpos($low, $needle) !== false) return $cls;
            }
            return null;
        };
    @endphp
    
    @php
        $popularTabs = collect([['key' => 'all', 'label' => 'Полный', 'genre' => null]]);
        $topGenres = \App\Models\Genre::withCount('novels')
            ->orderByDesc('novels_count')
            ->take(10)->get();
        foreach ($topGenres as $g) {
            $popularTabs->push(['key' => 'g' . $g->id, 'label' => $g->name, 'genre' => $g->id]);
        }

        $popularBaseQ = fn() => \App\Models\Novel::where('is_published', true)
            ->when($isRu_C,      fn($q) => $q->where('is_restricted', false))
            ->when($hideAdult_C, fn($q) => $q->where('is_adult', false))
            ->when($isGuest_C,   fn($q) => $q->where('hide_from_guests', false))
            ->with(['genres', 'publisher:id,name'])
            ->withCount('chapters');

        $popularPerTab = ['realtime' => [], 'weekly' => []];

        foreach ($popularTabs as $t) {
            $popularPerTab['realtime'][$t['key']] = $popularBaseQ()
                ->when($t['genre'], fn($q) => $q->whereHas('genres', fn($gq) => $gq->where('genres.id', $t['genre'])))
                ->orderByDesc('views')
                ->take(9)->get();

            $popularPerTab['weekly'][$t['key']] = $popularBaseQ()
                ->withCount(['viewsLog as views_7d' => fn($q) => $q->where('viewed_at', '>=', now()->subDays(7)->toDateString())])
                ->when($t['genre'], fn($q) => $q->whereHas('genres', fn($gq) => $gq->where('genres.id', $t['genre'])))
                ->orderByDesc('views_7d')
                ->take(9)->get();
        }
    @endphp
    @if(!empty($popularPerTab['realtime']['all']) && $popularPerTab['realtime']['all']->count())
        <section class="eri-section" x-data="{ popTab: 'all', popMode: 'realtime' }">
            <div class="np-pop-head">
                <div>
                    <h2 class="np-pop-title">Популярные произведения</h2>
                    <p class="np-pop-sub">
                        <span x-show="popMode === 'realtime'">Обновляется в реальном времени · по просмотрам</span>
                        <span x-show="popMode === 'weekly'" x-cloak>Лучшее за 7 дней · по просмотрам</span>
                    </p>
                </div>
                <div class="np-pop-mode">
                    <button type="button" :class="popMode === 'realtime' ? 'on' : ''" @click="popMode = 'realtime'">Реальное время</button>
                    <button type="button" :class="popMode === 'weekly'   ? 'on' : ''" @click="popMode = 'weekly'">Еженедельно</button>
                </div>
            </div>

            <div class="np-pop-tabs">
                @foreach($popularTabs as $t)
                    <button type="button" class="np-pop-tab"
                            :class="popTab === '{{ $t['key'] }}' ? 'on' : ''"
                            @click="popTab = '{{ $t['key'] }}'">{{ $t['label'] }}</button>
                @endforeach
            </div>

            @foreach(['realtime', 'weekly'] as $mode)
                @foreach($popularTabs as $t)
                    @php $items = $popularPerTab[$mode][$t['key']] ?? collect(); @endphp
                    @if($items->count())
                        <div x-show="popMode === '{{ $mode }}' && popTab === '{{ $t['key'] }}'"
                             @if(!($mode === 'realtime' && $t['key'] === 'all')) x-cloak @endif
                             class="np-pop-grid">
                            @foreach($items as $i => $n)
                                <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="np-pop-row">
                                    <div class="np-pop-cover">
                                        <x-eriiba.cover :novel="$n" />
                                    </div>
                                    <div class="np-pop-rank">{{ $i + 1 }}</div>
                                    <div class="np-pop-info">
                                        <div class="np-pop-name">{{ $n->title }}</div>
                                        <div class="np-pop-author">{{ $n->publisher->name ?? '—' }}</div>
                                        <div class="np-pop-genres">
                                            @foreach($n->genres->take(3) as $g)
                                                <span>{{ $g->name }}</span>
                                                @if(!$loop->last)<span class="np-pop-genres-dot">·</span>@endif
                                            @endforeach
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endforeach
        </section>
    @endif

    @foreach($blocks as $block)
        @php $type = $block['type'] ?? 'block'; @endphp

        @if($type === 'continue') @continue @endif

        @if(($type === 'block' || $type === 'collection') && !empty($block['data']) && $block['data']->count())
            <section class="eri-section">
                <x-eriiba.section-head :title="$block['title'] ?? 'Подборка'" :more="route('catalog', ['sort' => $block['sort'] ?? 'latest'])" />

                @if(($block['layout'] ?? 'grid') === 'list')
                    <div class="ch-table">
                        @foreach($block['data']->take(10) as $novel)
                            <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="ch-row" style="grid-template-columns: 56px 1fr auto;">
                                <x-eriiba.cover :novel="$novel" />
                                <div>
                                    <div class="ch-title">{{ $novel->title }}</div>
                                    <div class="ch-meta" style="justify-content:flex-start;">
                                        @if(isset($novel->chapters_count))<span>{{ $novel->chapters_count }} глав</span>@endif
                                        @if(($novel->avg_rating ?? 0) > 0)<span>★ {{ number_format((float) $novel->avg_rating, 1) }}</span>@endif
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right" style="color:var(--text-muted)"></i>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="eri-grid-row">
                        @foreach($block['data']->take(($block['rows'] ?? 1) * 6) as $i => $novel)
                            <x-eriiba.novel-card :novel="$novel" :rank="($block['show_rank'] ?? false) ? $i+1 : null" />
                        @endforeach
                    </div>
                @endif
            </section>

            @if(isset($adBanners[$adIndex]))
                <section class="eri-section" style="padding-top:0;padding-bottom:14px;">
                    <div style="position:relative;border-radius:var(--r-md);overflow:hidden;background:var(--surface);border:1px solid var(--border);max-height:140px;">
                        <img src="{{ $adBanners[$adIndex]->image_url }}" alt="Реклама" loading="lazy"
                             style="width:100%;height:140px;object-fit:cover;display:block;">
                        @if($adBanners[$adIndex]->url)<a href="{{ $adBanners[$adIndex]->url }}" target="_blank" style="position:absolute;inset:0;"></a>@endif
                        <div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.55);color:#fff;font-size:10px;padding:2px 8px;border-radius:999px;">Реклама</div>
                    </div>
                </section>
                @php $adIndex++; @endphp
            @endif

        @elseif($type === 'favorites' && !empty($block['data']) && $block['data']->count())
            <section class="eri-section">
                <x-eriiba.section-head :title="$block['title'] ?? 'Обновления избранного'" />
                <div class="ch-table">
                    @foreach($block['data']->take(8) as $chapter)
                        <a href="{{ route('novel.read', [$chapter->novel->id, $chapter->id]) }}" wire:navigate class="ch-row" style="grid-template-columns: 56px 1fr auto;">
                            <x-eriiba.cover :novel="$chapter->novel" />
                            <div>
                                <div class="ch-title" style="font-family:var(--display);font-size:15px;">{{ $chapter->novel->title }}</div>
                                <div style="font-family:var(--serif);font-size:13px;color:var(--text-2);margin-top:2px;">{{ $chapter->title }}</div>
                            </div>
                            <span class="ch-num">{{ \Carbon\Carbon::parse($chapter->published_at ?? $chapter->created_at)->diffForHumans(null, true, true) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

        @elseif($type === 'schedule' && !empty($block['data']) && $block['data']->count())
            <section class="eri-section">
                <x-eriiba.section-head :title="$block['title'] ?? 'Расписание'" sub="следующие выходы" />
                <div class="ch-table">
                    @foreach($block['data']->take(8) as $chapter)
                        <div class="ch-row" style="grid-template-columns: 56px 1fr auto;">
                            <x-eriiba.cover :novel="$chapter->novel" />
                            <div>
                                <div class="ch-title" style="font-family:var(--display);font-size:15px;">{{ $chapter->novel->title }}</div>
                                <div style="font-family:var(--serif);font-size:13px;color:var(--text-2);margin-top:2px;">{{ $chapter->title }}</div>
                            </div>
                            <span class="eri-chip warn"><i class="fa-solid fa-clock" style="font-size:9px;"></i> {{ $chapter->published_at->format('d.m в H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

        @elseif($type === 'banner_custom' && !empty($block['custom_content']))
            <section class="eri-section">
                <div style="border-radius:var(--r-md);overflow:hidden;border:1px solid var(--border);">{!! $block['custom_content'] !!}</div>
            </section>
        @endif
    @endforeach

    @php $bigAds = ($adBanners ?? collect())->slice($adIndex ?? 0, 2)->values(); @endphp
    @if($bigAds->count() >= 1)
        <section class="eri-section" style="padding-top:8px;">
            <div class="home-big-ads">
                @foreach($bigAds as $ad)
                    @php
                        $href = $ad->url ?? '#';
                        $isExt = $href !== '#' && (str_starts_with($href, 'http://') || str_starts_with($href, 'https://'));
                    @endphp
                    <a href="{{ $href }}" @if($isExt) target="_blank" rel="noopener" @else wire:navigate @endif class="home-big-ad">
                        <img src="{{ $ad->image_url }}" alt="Реклама" loading="lazy">
                    </a>
                @endforeach
            </div>
            @php $adIndex = ($adIndex ?? 0) + $bigAds->count(); @endphp
        </section>
    @endif

    @php
        $isGuestPD   = !\Illuminate\Support\Facades\Auth::check();
        $hideAdultPD = $isGuestPD && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
        $pdPicks = \App\Models\Novel::where('is_published', true)
            ->when($hideAdultPD, fn($q) => $q->where('is_adult', false))
            ->when($isGuestPD,   fn($q) => $q->where('hide_from_guests', false))
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
            ->whereHas('ratings')
            ->orderByDesc('avg_rating')
            ->with(['genres', 'publisher:id,name'])
            ->withCount('chapters')
            ->take(2)->get();
    @endphp
    @if($pdPicks->count() >= 2)
        <section class="eri-section">
            <x-eriiba.section-head title="Выбор редакции" sub="что почитать сегодня — рекомендации команды сайта" :more="route('rankings')" moreLabel="Показать ещё →" />
            <div class="pd-picks">
                @foreach($pdPicks as $pd)
                    @php
                        $syn = trim(strip_tags($pd->synopsis ?? $pd->description ?? ''));
                        $syn = preg_replace('/\s+/u', ' ', $syn);
                        $syn = \Illuminate\Support\Str::words($syn, 22, '…');
                        $genreNames = $pd->genres->take(3)->pluck('name')->implode(' · ');
                    @endphp
                    <a href="{{ route('novel.show', $pd->id) }}" wire:navigate class="pd-pick-card">
                        <div class="pd-pick-cover">
                            <x-eriiba.cover :novel="$pd" />
                        </div>
                        <div class="pd-pick-info">
                            <div class="pd-pick-author">{{ $pd->publisher->name ?? 'Автор' }}</div>
                            <div class="pd-pick-title">{{ $pd->title }}</div>
                            @if($genreNames)
                                <div class="pd-pick-genres">{{ $genreNames }}</div>
                            @endif
                            @if($syn)
                                <div class="pd-pick-body">{{ $syn }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $isGuestM   = !\Illuminate\Support\Facades\Auth::check();
        $hideAdultM = $isGuestM && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
        $isRuM      = \App\Services\GeoService::isRu(request()->ip());
        $millionPicks = \App\Models\Novel::where('is_published', true)
            ->when($isRuM,      fn($q) => $q->where('is_restricted', false))
            ->when($hideAdultM, fn($q) => $q->where('is_adult', false))
            ->when($isGuestM,   fn($q) => $q->where('hide_from_guests', false))
            ->orderByDesc('views')
            ->take(7)->get();
    @endphp
    @if($millionPicks->count() >= 4)
        <section class="eri-section">
            <x-eriiba.section-head title="Выбор миллионов" sub="Шедевры, которые выбрали читатели" :more="route('catalog')" moreLabel="Показать ещё →" />
            <div class="million-row">
                @foreach($millionPicks as $m)
                    <a href="{{ route('novel.show', $m->id) }}" wire:navigate class="million-card">
                        <div class="million-cover">
                            <x-eriiba.cover :novel="$m" />
                        </div>
                        <div class="million-title">{{ \Illuminate\Support\Str::limit($m->title, 32, '…') }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $pickAds = ($adBanners ?? collect())->slice($adIndex ?? 0, 3)->values();
        $pickNovels = \App\Models\Novel::where('is_published', true)
            ->when($isRu_W,      fn($q) => $q->where('is_restricted', false))
            ->when($hideAdult_W, fn($q) => $q->where('is_adult', false))
            ->when($isGuest_W,   fn($q) => $q->where('hide_from_guests', false))
            ->withCount(['ratings as avg_rating' => fn($q) => $q->select(\Illuminate\Support\Facades\DB::raw('coalesce(avg(score),0)'))])
            ->whereHas('ratings')
            ->with(['genres', 'publisher:id,name'])
            ->orderByDesc('avg_rating')
            ->skip(2)->take(9)->get();
    @endphp
    @if($pickNovels->count() >= 3)
        <section class="eri-section">
            <x-eriiba.section-head title="Подборка лучшего"
                                   sub="Если не знаете, что почитать — загляните сюда"
                                   :more="route('rankings')" moreLabel="Показать ещё →" />

            @if($pickAds->count() >= 1)
                <div class="np-pick-banners">
                    @foreach($pickAds as $ad)
                        @php
                            $href = $ad->url ?? '#';
                            $isExt = $href !== '#' && (str_starts_with($href, 'http://') || str_starts_with($href, 'https://'));
                        @endphp
                        <a href="{{ $href }}" @if($isExt) target="_blank" rel="noopener" @else wire:navigate @endif class="np-pick-banner">
                            <img src="{{ $ad->image_url }}" alt="" loading="lazy">
                        </a>
                    @endforeach
                </div>
                @php $adIndex = ($adIndex ?? 0) + $pickAds->count(); @endphp
            @endif

            <div class="np-pick-grid">
                @foreach($pickNovels as $n)
                    <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="np-pick-row">
                        <div class="np-pick-cover">
                            <x-eriiba.cover :novel="$n" />
                        </div>
                        <div class="np-pick-info">
                            <div class="np-pick-name">{{ $n->title }}</div>
                            <div class="np-pick-author">{{ $n->publisher->name ?? '—' }}</div>
                            <div class="np-pick-genres">
                                @foreach($n->genres->take(3) as $g)
                                    <span>{{ $g->name }}</span>
                                    @if(!$loop->last)<span class="np-pick-dot">·</span>@endif
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($featuredNovels) && $featuredNovels->isNotEmpty())
        @php $editorial = $featuredNovels->last(); @endphp
        <section class="eri-section">
            <div class="editorial">
                <div class="editorial-cover">
                    <x-eriiba.cover :novel="$editorial" />
                </div>
                <div>
                    <div class="editorial-eyebrow">Выбор редакции · {{ now()->translatedFormat('F') }}</div>
                    <h2 class="editorial-title">«{{ $editorial->title }}»</h2>
                    @php
                        $synEd = strip_tags($editorial->synopsis ?? $editorial->description ?? '');
                        $synEd = preg_replace('/[*_`#>]+/u', '', $synEd);
                        $synEd = preg_replace('/\s+/u', ' ', trim($synEd));
                        $synEd = \Illuminate\Support\Str::words($synEd, 32, '…');
                    @endphp
                    @if($synEd)
                        <p class="editorial-quote">{{ $synEd }}</p>
                    @endif
                    <div class="editorial-meta">
                        <span>{{ $editorial->publisher->name ?? 'Автор' }}</span>
                        <span>·</span>
                        <span>{{ $editorial->chapters_count ?? 0 }} глав</span>
                    </div>
                    <div class="editorial-actions">
                        <a class="eri-btn primary" href="{{ route('novel.show', $editorial->id) }}" wire:navigate>Открыть страницу</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @php
        $isGuestHot   = !\Illuminate\Support\Facades\Auth::check();
        $hideAdultHot = $isGuestHot && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
        $isRuHot      = \App\Services\GeoService::isRu(request()->ip());

        $hotPlus = \App\Models\Novel::where('is_published', true)
            ->where('price', '>', 0)
            ->when($isRuHot,    fn($q) => $q->where('is_restricted', false))
            ->when($hideAdultHot, fn($q) => $q->where('is_adult', false))
            ->when($isGuestHot, fn($q) => $q->where('hide_from_guests', false))
            ->withCount('chapters')
            ->latest()->take(6)->get();

        $hotCompleted = \App\Models\Novel::where('is_published', true)
            ->where('status', 'completed')
            ->when($isRuHot,    fn($q) => $q->where('is_restricted', false))
            ->when($hideAdultHot, fn($q) => $q->where('is_adult', false))
            ->when($isGuestHot, fn($q) => $q->where('hide_from_guests', false))
            ->withCount('chapters')
            ->latest()->take(6)->get();
    @endphp
    @if($hotPlus->count() || $hotCompleted->count())
        <section class="eri-section" x-data="{ hotTab: '{{ $hotPlus->count() ? 'plus' : 'completed' }}' }">
            <x-eriiba.section-head title="Горячая новая работа" sub="свежие новинки в Plus и завершённые истории" :more="route('catalog')" moreLabel="Весь каталог →" />

            <div class="home-hot-tabs">
                @if($hotPlus->count())
                    <button type="button" class="home-hot-tab" :class="hotTab==='plus' ? 'on' : ''" @click="hotTab='plus'">
                        <i class="fa-solid fa-crown" style="margin-right:5px;color:#f59e0b;"></i>Новый Plus работает
                    </button>
                @endif
                @if($hotCompleted->count())
                    <button type="button" class="home-hot-tab" :class="hotTab==='completed' ? 'on' : ''" @click="hotTab='completed'">
                        <i class="fa-solid fa-flag-checkered" style="margin-right:5px;"></i>Новые завершённые
                    </button>
                @endif
            </div>

            @if($hotPlus->count())
                <div x-show="hotTab==='plus'" class="home-hot-grid">
                    @foreach($hotPlus as $n)
                        <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="continue-card" style="padding:0;flex-direction:column;align-items:stretch;background:transparent;border:0;">
                            <div class="continue-cover" style="width:100%;aspect-ratio:2/3;margin-bottom:8px;">
                                <x-eriiba.cover :novel="$n" />
                            </div>
                            <div class="continue-title" style="font-size:13px;line-height:1.3;">{{ $n->title }}</div>
                            <div class="continue-chapter" style="font-size:11px;">{{ $n->chapters_count }} гл.</div>
                        </a>
                    @endforeach
                </div>
            @endif
            @if($hotCompleted->count())
                <div x-show="hotTab==='completed'" x-cloak class="home-hot-grid">
                    @foreach($hotCompleted as $n)
                        <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="continue-card" style="padding:0;flex-direction:column;align-items:stretch;background:transparent;border:0;">
                            <div class="continue-cover" style="width:100%;aspect-ratio:2/3;margin-bottom:8px;">
                                <x-eriiba.cover :novel="$n" />
                            </div>
                            <div class="continue-title" style="font-size:13px;line-height:1.3;">{{ $n->title }}</div>
                            <div class="continue-chapter" style="font-size:11px;">Завершено · {{ $n->chapters_count }} гл.</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @php
        $hasReviewsRoutes = \Illuminate\Support\Facades\Route::has('reviews.index')
            && \Illuminate\Support\Facades\Route::has('reviews.show');
        $hasReviewsTable = $hasReviewsRoutes && \Illuminate\Support\Facades\Schema::hasTable('reviews');
        $reviewsLatest = $hasReviewsTable
            ? \App\Models\Review::where('is_published', true)
                ->where('category', 'review')
                ->where('is_pinned', false)
                ->with('user:id,name,username,avatar')
                ->latest()->take(4)->get()
            : collect();
        $reviewsPopular = $hasReviewsTable
            ? \App\Models\Review::where('is_published', true)
                ->where('category', 'review')
                ->where('is_pinned', false)
                ->with('user:id,name,username,avatar')
                ->orderByDesc('recommends_count')
                ->orderByDesc('views_count')
                ->take(4)->get()
            : collect();
    @endphp
    @if($reviewsLatest->count())
        <section class="eri-section" x-data="{ revMode: 'latest' }">
            <div class="np-pop-head" style="margin-bottom:14px;">
                <div>
                    <h2 class="np-pop-title">Отзывы читателей</h2>
                    <p class="np-pop-sub">Что говорят те, кто уже прочёл</p>
                </div>
                <div class="np-pop-mode">
                    <button type="button" :class="revMode === 'latest'  ? 'on' : ''" @click="revMode = 'latest'">Свежие</button>
                    <button type="button" :class="revMode === 'popular' ? 'on' : ''" @click="revMode = 'popular'">Популярные</button>
                </div>
            </div>

            @foreach(['latest' => $reviewsLatest, 'popular' => $reviewsPopular] as $mode => $list)
                @if($list->count())
                    <div x-show="revMode === '{{ $mode }}'" @if($mode !== 'latest') x-cloak @endif class="home-reviews-grid">
                        @foreach($list as $r)
                            @php
                                $plainBody = trim(strip_tags($r->body));
                                $shortBody = \Illuminate\Support\Str::words($plainBody, 22, '…');
                            @endphp
                            <a href="{{ route('reviews.show', $r->id) }}" wire:navigate class="home-review-card">
                                <div class="home-review-author">
                                    @if($r->user?->avatar_url)
                                        <img src="{{ $r->user->avatar_url }}" alt="" class="home-review-ava">
                                    @else
                                        <span class="home-review-ava home-review-ava-text">{{ mb_strtoupper(mb_substr($r->user?->name ?? '?', 0, 1)) }}</span>
                                    @endif
                                    <div class="home-review-author-name">{{ $r->user?->name ?? '—' }}</div>
                                </div>
                                <div class="home-review-title">«{{ $r->title }}»</div>
                                @if($shortBody)<div class="home-review-body">{{ $shortBody }}</div>@endif
                                <div class="home-review-stats">
                                    <span><i class="fa-regular fa-thumbs-up"></i> {{ $r->recommends_count }}</span>
                                    <span><i class="fa-regular fa-eye"></i> {{ $r->views_count }}</span>
                                    <span>{{ $r->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach

            <div style="text-align:center;margin-top:14px;">
                <a href="{{ route('reviews.index') }}" wire:navigate class="eri-btn sm">Все обзоры →</a>
            </div>
        </section>
    @endif

    @php
        $homeEvents = \App\Models\NovelPromotion::active()
            ->with(['novel:id,title,slug,cover_image'])
            ->latest('starts_at')->take(3)->get();
    @endphp
    @if($homeEvents->count())
        <section class="eri-section">
            <x-eriiba.section-head title="События в разгаре" sub="Активные продвижения и промо-кампании" />
            <div class="home-events">
                @foreach($homeEvents as $ev)
                    @if($ev->novel)
                        <a href="{{ route('novel.show', $ev->novel->id) }}" wire:navigate class="home-event-card placeholder">
                            @if($ev->novel->cover_image)
                                <img src="{{ \App\Models\Novel::storageUrl($ev->novel->cover_image) }}" alt="" loading="lazy">
                            @endif
                            <div class="overlay">
                                <div class="title">{{ $ev->novel->title }}</div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    @php
        $isGuestLast = !\Illuminate\Support\Facades\Auth::check();
        $hideAdultLast = $isGuestLast && filter_var(\App\Models\Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);
        $latestChaptersRaw = \App\Models\Chapter::published()
            ->with(['novel' => fn($q) => $q->select('id','title','cover_image','slug','user_id','is_adult','hide_from_guests')
                ->withCount(['chapters as total_chapters_count' => fn($q2) => $q2->published()])
                ->with('publisher:id,name,username')])
            ->when($isGuestLast, fn($q) => $q->whereHas('novel', fn($nq) => $nq->where('hide_from_guests', false)))
            ->when($hideAdultLast, fn($q) => $q->whereHas('novel', fn($nq) => $nq->where('is_adult', false)))
            ->select(['chapters.id','chapters.novel_id','chapters.title','chapters.sort_order','chapters.is_locked','chapters.is_published','chapters.published_at','chapters.created_at'])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take(100)->get();
        $latestByNovel = $latestChaptersRaw->unique('novel_id')->take(12)->values();
    @endphp
    @if($latestByNovel->count())
        <section class="eri-section">
            <x-eriiba.section-head title="Последние обновления" sub="новеллы со свежими главами" :more="route('updates')" moreLabel="Все обновления →" />
            <div class="updates-list">
                @foreach($latestByNovel as $chapter)
                    @if($chapter->novel)
                        @php
                            $whenAgo = \Carbon\Carbon::parse($chapter->published_at ?? $chapter->created_at)->diffForHumans(null, true, true);
                            $totalCh = $chapter->novel->total_chapters_count ?? 0;
                        @endphp
                        <a href="{{ route('novel.show', $chapter->novel->id) }}" wire:navigate class="updates-row">
                            <div class="updates-cover">
                                <x-eriiba.cover :novel="$chapter->novel" :lock="$chapter->is_locked ?? false" />
                            </div>
                            <div class="updates-info">
                                <div class="updates-novel" title="{{ $chapter->novel->title }}">{{ $chapter->novel->title }}</div>
                                <div class="updates-chapter" title="{{ $chapter->title }}">
                                    <i class="fa-solid fa-circle-arrow-up"></i>
                                    <span class="updates-chapter-name">{{ $chapter->title }}</span>
                                    <span class="updates-chapter-time">· {{ $whenAgo }}</span>
                                </div>
                            </div>
                            <div class="updates-count" title="Всего глав в новелле">
                                <div class="updates-count-num">{{ $totalCh }}</div>
                                <div class="updates-count-label">глав</div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
