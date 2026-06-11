@extends('layouts.app')
@section('title', 'Профиль · ' . $user->name)

@section('content')
@php
    $isAuthor = $user->hasRole('author') || $user->hasRole('super_admin');
    $favCount = $favorites->count();
    $histCount = $history->count();
    $reviewsCount = method_exists($user, 'reviews') ? $user->reviews()->count() : 0;
    $purchasesCount = \App\Models\Subscription::where('user_id', $user->id)->where('status', 'active')->count();
    $totalChapters = $history->sum('percent') > 0 ? $history->count() : $histCount;
    $tabs = [
        ['key' => 'history',      'label' => 'История'],
        ['key' => 'favorites',    'label' => 'Избранное', 'count' => $favCount],
        ['key' => 'purchases',    'label' => 'Подписки',  'count' => $purchasesCount],
    ];
    if ($isAuthor) {
        $tabs[] = ['key' => 'requests', 'label' => 'Заявки'];
    }
    $tabs[] = ['key' => 'transactions', 'label' => 'Финансы'];
    $tabs[] = ['key' => 'settings',     'label' => 'Настройки'];

    $initials = mb_strtoupper(mb_substr($user->name, 0, 2));
@endphp

<div x-data="{ tab: window.location.hash.slice(1) || 'history' }">

    <div class="profile-banner">
        <div class="profile-banner-inner">
            <div class="profile-avatar" style="background:linear-gradient(135deg,#8aabff,#2f6df0);overflow:hidden;position:relative;">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                @else
                    {{ $initials }}
                @endif
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                      style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.55);opacity:0;transition:opacity .2s;cursor:pointer;"
                      onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                    @csrf
                    <label style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;cursor:pointer;color:#fff;">
                        <i class="fa-solid fa-camera" style="font-size:18px;"></i>
                        <span style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Сменить</span>
                        <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="this.form.submit()">
                    </label>
                </form>
            </div>

            <div class="profile-meta">
                @if($user->username)
                    <div class="profile-username">{{ '@' . $user->username }} · ID {{ $user->id }}</div>
                @else
                    <div class="profile-username">ID {{ $user->id }}</div>
                @endif
                <h1 class="profile-name">{{ $user->name }}</h1>

                @if($user->bio)
                    <p class="profile-bio">{{ $user->bio }}</p>
                @endif

                <div class="profile-tags">
                    <span class="pin"><i class="fa-solid fa-envelope"></i>{{ $user->email }}</span>
                    @if($user->hasVerifiedEmail())
                        <span class="pin" style="color:var(--ok);"><i class="fa-solid fa-circle-check"></i>почта подтверждена</span>
                    @else
                        <a href="{{ route('verification.notice') }}" class="pin" style="color:var(--warn);text-decoration:none;">
                            <i class="fa-solid fa-triangle-exclamation"></i>подтвердить почту
                        </a>
                    @endif
                    @if($user->hasRole('owner'))        <span class="pin" style="color:var(--gold,#b97834);"><i class="fa-solid fa-crown"></i>Руководство</span> @endif
                    @if($user->hasRole('super_admin'))  <span class="pin" style="color:var(--err);"><i class="fa-solid fa-shield-halved"></i>Администратор</span> @endif
                    @if($user->hasRole('deputy_admin')) <span class="pin"><i class="fa-solid fa-shield"></i>Зам. администратора</span> @endif
                    @if($user->hasRole('moderator'))    <span class="pin"><i class="fa-solid fa-gavel"></i>Модератор</span> @endif
                    @if($user->hasRole('editor'))       <span class="pin"><i class="fa-solid fa-pen-nib"></i>Редактор</span> @endif
                    @if($user->hasRole('author'))       <span class="pin" style="color:var(--accent);"><i class="fa-solid fa-feather"></i>Автор</span> @endif
                </div>
            </div>

            <div class="profile-actions">
                @if($isAuthor)
                    <a href="{{ route('my-novels') }}" wire:navigate class="eri-btn primary">
                        <i class="fa-solid fa-book-open"></i> Мои работы
                    </a>
                @endif
                <button type="button" class="eri-btn" @click="tab='settings'; window.location.hash='settings'">
                    <i class="fa-solid fa-gear"></i> Настройки
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="eri-btn ghost">
                        <i class="fa-solid fa-right-from-bracket"></i> Выйти
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <div class="stat-card">
            <div class="lbl">В библиотеке</div>
            <div class="val">{{ $favCount }}</div>
            <div class="delta neg">избранных новелл</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Читаю</div>
            <div class="val">{{ $histCount }}</div>
            <div class="delta neg">в процессе</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Подписки</div>
            <div class="val">{{ $purchasesCount }}</div>
            <div class="delta neg">активных</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Баланс</div>
            <div class="val">{{ number_format((float)$user->balance, 0, ',', ' ') }}</div>
            <div class="delta neg">рублей</div>
        </div>
        <div class="stat-card">
            <div class="lbl">С нами с</div>
            <div class="val" style="font-size:22px;">{{ $user->created_at?->translatedFormat('M Y') ?? '—' }}</div>
            <div class="delta neg">{{ $user->created_at?->diffForHumans(['parts' => 1, 'short' => true]) ?? '' }}</div>
        </div>
    </div>

    <div class="profile-body" style="grid-template-columns: 1fr;">
        <div>
            <div class="profile-tabs">
                @foreach($tabs as $t)
                    <button type="button"
                            @click="tab = '{{ $t['key'] }}'; window.location.hash = '{{ $t['key'] }}'"
                            :class="tab === '{{ $t['key'] }}' ? 'on' : ''">
                        {{ $t['label'] }}
                        @if(isset($t['count']) && $t['count'] > 0)
                            <span class="count">{{ $t['count'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div x-show="tab === 'history'" x-transition>
                @if($history->isEmpty())
                    <div class="eri-card eri-card-pad-lg" style="text-align:center;padding:60px 24px;">
                        <i class="fa-solid fa-book-open" style="font-size:36px;color:var(--text-muted);margin-bottom:12px;"></i>
                        <p style="color:var(--text-3);font-family:var(--serif);font-size:15px;">История чтения пуста.</p>
                        <a href="{{ route('catalog') }}" wire:navigate class="eri-btn primary sm" style="margin-top:14px;">Перейти в каталог</a>
                    </div>
                @else
                    <div style="display:grid;gap:12px;">
                        @foreach($history as $progress)
                            @if($progress->novel)
                                <a href="{{ route('novel.read', [$progress->novel->id, $progress->chapter_id]) }}"
                                   class="reading-now" style="text-decoration:none;color:inherit;">
                                    <div class="cover" style="border-radius:var(--r-sm);overflow:hidden;background:var(--surface-2);">
                                        @if($progress->novel->cover_image)
                                            <img src="{{ \App\Models\Novel::storageUrl($progress->novel->cover_image) }}"
                                                 alt="" style="width:100%;height:100%;object-fit:cover;">
                                        @endif
                                    </div>
                                    <div>
                                        <h4>{{ $progress->novel->title }}</h4>
                                        <div class="author">{{ $progress->chapter ? $progress->chapter->title : 'Глава' }}</div>
                                        <div class="progress"><i style="width: {{ $progress->percent ?? 0 }}%"></i></div>
                                        <div class="progress-meta">{{ $progress->percent ?? 0 }}% · продолжить чтение</div>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div x-show="tab === 'favorites'" style="display:none;" x-transition>
                @if($favorites->isEmpty())
                    <div class="eri-card eri-card-pad-lg" style="text-align:center;padding:60px 24px;">
                        <i class="fa-solid fa-heart" style="font-size:36px;color:var(--text-muted);margin-bottom:12px;"></i>
                        <p style="color:var(--text-3);font-family:var(--serif);font-size:15px;">Список избранного пуст.</p>
                        <a href="{{ route('catalog') }}" wire:navigate class="eri-btn primary sm" style="margin-top:14px;">Найти новеллы</a>
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
                        @foreach($favorites as $novel)
                            @include('partials.novel-card-modern', ['novel' => $novel])
                        @endforeach
                    </div>
                @endif
            </div>

            <div x-show="tab === 'purchases'" style="display:none;" x-transition>
                @include('partials.profile-purchases')
            </div>

            @if($isAuthor)
                <div x-show="tab === 'requests'" style="display:none;" x-transition>
                    @livewire('profile-requests')
                </div>
            @endif

            <div x-show="tab === 'transactions'" style="display:none;" x-transition>
                @include('partials.profile-transactions')
            </div>

            <div x-show="tab === 'settings'" style="display:none;" x-transition>
                @include('partials.profile-settings')
            </div>
        </div>
    </div>
</div>
@endsection
