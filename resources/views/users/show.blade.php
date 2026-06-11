@extends('layouts.app')
@section('title', $user->name)

@section('content')
@php
    $bannerHeight = \App\Models\Setting::retrieve('profile_banner_height', 250);

    $isAuthor = $user->novels->count() > 0 || $user->hasRole('author');
    $novelsCount = $user->novels->count();
    $totalChapters = (int) $user->novels->sum(function ($n) { return $n->chapters_count ?? 0; });
    $totalViews = (int) $user->novels->sum('views');
    $totalFavorites = \App\Models\Novel::where('user_id', $user->id)->withCount('favorites')->get()->sum('favorites_count');
    $avgRating = $user->novels->where('avg_rating', '>', 0)->avg('avg_rating');

    $latestChapters = $isAuthor
        ? \App\Models\Chapter::whereHas('novel', fn($q) => $q->where('user_id', $user->id)->where('is_published', true))
            ->published()
            ->with('novel')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take(12)
            ->get()
        : collect();

    $initial = mb_strtoupper(mb_substr($user->name ?? '?', 0, 2));
    $joined = $user->created_at ? $user->created_at->locale('ru')->isoFormat('MMMM YYYY') : null;

    $roleBadges = [
        'owner'        => 'Руководство',
        'super_admin'  => 'Администратор',
        'deputy_admin' => 'Зам. администратора',
        'moderator'    => 'Модератор',
        'editor'       => 'Редактор',
        'author'       => 'Автор',
    ];
@endphp

<div class="eri-page">

    <div class="profile-banner">
        @if($user->banner_image)
            <div style="position:absolute; inset:0; opacity:0.55;">
                <img src="{{ $user->banner_url }}" alt="" fetchpriority="high" loading="eager" decoding="async" style="width:100%; height:100%; object-fit:cover;">
            </div>
        @endif
        <div class="profile-banner-inner" style="position:relative; z-index:1;">
            <div class="profile-avatar">
                @if($user->avatar)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" fetchpriority="high" loading="eager" decoding="async">
                @else
                    {{ $initial }}
                @endif
            </div>

            <div class="profile-meta">
                @if($user->username)
                    <div class="profile-username">{{ '@' . $user->username }}</div>
                @endif
                <h1 class="profile-name">{{ $user->name }}</h1>
                @if($user->bio)
                    <p class="profile-bio">{{ $user->bio }}</p>
                @endif
                <div class="profile-tags">
                    @foreach($roleBadges as $role => $label)
                        @if($user->hasRole($role))
                            <span class="pin"><i class="fa-solid fa-shield-halved"></i> {{ $label }}</span>
                        @endif
                    @endforeach
                    @if($joined)
                        <span class="pin"><i class="fa-regular fa-calendar"></i> с {{ $joined }}</span>
                    @endif
                    @if($user->social_link)
                        <a class="pin" href="{{ $user->social_link }}" target="_blank" rel="nofollow noopener">
                            <i class="fa-brands fa-telegram"></i> Связь
                        </a>
                    @endif
                    @if($user->donation_link && $user->is_donation_link_approved)
                        <a class="pin" href="{{ $user->donation_link }}" target="_blank" rel="nofollow noopener">
                            <i class="fa-solid fa-heart"></i> Поддержать
                        </a>
                    @endif
                </div>
            </div>

            <div class="profile-actions">
                @auth
                    @if(Auth::id() !== $user->id)
                        <x-eriiba.btn variant="primary" :href="route('messages.user', $user->id)" icon="envelope" wire:navigate>
                            Связаться
                        </x-eriiba.btn>
                    @else
                        <x-eriiba.btn :href="route('profile')" icon="gear" wire:navigate>
                            Мой профиль
                        </x-eriiba.btn>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    @if($isAuthor)
        <div class="profile-stats">
            <div class="stat-card">
                <div class="lbl">Новелл</div>
                <div class="val">{{ $novelsCount }}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">Глав</div>
                <div class="val">{{ number_format($totalChapters, 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">Прочтений</div>
                <div class="val">
                    @if($totalViews >= 1000000) {{ round($totalViews / 1000000, 1) }}M
                    @elseif($totalViews >= 1000) {{ round($totalViews / 1000, 1) }}K
                    @else {{ $totalViews }} @endif
                </div>
            </div>
            <div class="stat-card">
                <div class="lbl">В избранном</div>
                <div class="val">{{ number_format($totalFavorites, 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card">
                <div class="lbl">Средний рейтинг</div>
                <div class="val">{{ $avgRating ? number_format($avgRating, 1) : '—' }}</div>
            </div>
        </div>
    @endif

    <div class="profile-body" x-data="{ tab: '{{ $isAuthor ? 'novels' : 'activity' }}' }">
        <div>
            <div class="profile-tabs">
                @if($isAuthor)
                    <button @click="tab = 'novels'" :class="tab === 'novels' ? 'on' : ''">
                        <i class="fa-solid fa-book"></i> Новеллы <span class="count">{{ $novelsCount }}</span>
                    </button>
                    <button @click="tab = 'chapters'" :class="tab === 'chapters' ? 'on' : ''">
                        <i class="fa-solid fa-clock-rotate-left"></i> Последние главы
                    </button>
                @endif
                <button @click="tab = 'activity'" :class="tab === 'activity' ? 'on' : ''">
                    <i class="fa-solid fa-wave-square"></i> Активность
                </button>
            </div>

            @if($isAuthor)
                <div x-show="tab === 'novels'" x-transition>
                    @if($user->novels->count())
                        <div class="eri-grid-row">
                            @foreach($user->novels as $novel)
                                <x-eriiba.novel-card :novel="$novel" />
                            @endforeach
                        </div>
                    @else
                        <div class="lib-empty">У автора пока нет опубликованных работ.</div>
                    @endif
                </div>

                <div x-show="tab === 'chapters'" x-transition style="display:none;">
                    @if($latestChapters->count())
                        <div class="ch-table">
                            @foreach($latestChapters as $chapter)
                                @include('partials.chapter-list-item', ['chapter' => $chapter])
                            @endforeach
                        </div>
                    @else
                        <div class="lib-empty">Глав пока нет.</div>
                    @endif
                </div>
            @endif

            <div x-show="tab === 'activity'" x-transition @if($isAuthor) style="display:none;" @endif>
                <div class="activity">
                    @if($joined)
                        <div class="activity-item">
                            <div class="activity-icon"><i class="fa-solid fa-user-plus"></i></div>
                            <div class="activity-text">Зарегистрирован{{ $user->gender === 'female' ? 'а' : '' }} на сайте</div>
                            <div class="activity-time">{{ $user->created_at->diffForHumans() }}</div>
                        </div>
                    @endif

                    @foreach($latestChapters->take(8) as $chapter)
                        <div class="activity-item">
                            <div class="activity-icon read"><i class="fa-solid fa-feather"></i></div>
                            <div class="activity-text">
                                Опубликовал{{ $user->gender === 'female' ? 'а' : '' }}
                                <strong>{{ $chapter->title }}</strong>
                                в «<a href="{{ route('novel.show', $chapter->novel->id) }}" wire:navigate style="color: var(--accent); text-decoration: none;">{{ $chapter->novel->title }}</a>»
                            </div>
                            <div class="activity-time">
                                {{ \Carbon\Carbon::parse($chapter->published_at ?? $chapter->created_at)->diffForHumans(null, true, true) }}
                            </div>
                        </div>
                    @endforeach

                    @if(!$isAuthor && !$joined)
                        <div class="lib-empty">Активности пока нет.</div>
                    @endif
                </div>
            </div>
        </div>

        <aside>
            <div class="side-card">
                <h3 style="font-family: var(--display); font-size: 16px; font-weight: 500; margin: 0 0 14px; color: var(--text); letter-spacing: -0.01em;">
                    О пользователе
                </h3>

                <div style="display: grid; gap: 10px; font-size: 13px;">
                    <div style="display:flex; justify-content: space-between;">
                        <span style="color: var(--text-3);">ID</span>
                        <span style="font-family: var(--mono); color: var(--text-2);">#{{ $user->id }}</span>
                    </div>
                    @if($joined)
                        <div style="display:flex; justify-content: space-between;">
                            <span style="color: var(--text-3);">Регистрация</span>
                            <span style="color: var(--text-2);">{{ $joined }}</span>
                        </div>
                    @endif
                    @if($isAuthor)
                        <div style="display:flex; justify-content: space-between;">
                            <span style="color: var(--text-3);">Работ</span>
                            <span style="color: var(--text-2);">{{ $novelsCount }}</span>
                        </div>
                        <div style="display:flex; justify-content: space-between;">
                            <span style="color: var(--text-3);">Всего глав</span>
                            <span style="color: var(--text-2);">{{ number_format($totalChapters, 0, '.', ' ') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @if($isAuthor && $user->novels->count())
                <div class="side-card" style="margin-top: 14px;">
                    <h3 style="font-family: var(--display); font-size: 16px; font-weight: 500; margin: 0 0 14px; color: var(--text); letter-spacing: -0.01em;">
                        Топ работ
                    </h3>
                    @foreach($user->novels->sortByDesc('views')->take(5) as $novel)
                        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate
                           class="reading-now" style="grid-template-columns: 56px 1fr; padding: 10px; margin-bottom: 8px;">
                            <x-eriiba.cover :novel="$novel" />
                            <div style="min-width:0;">
                                <h4 style="font-size: 14px; margin: 0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $novel->title }}
                                </h4>
                                <div class="progress-meta">
                                    {{ $novel->chapters_count ?? 0 }} гл · {{ number_format($novel->views ?? 0, 0, '.', ' ') }} прочтений
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
