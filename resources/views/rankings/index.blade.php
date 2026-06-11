@extends('layouts.app')
@section('title', 'Рейтинги — eriiba')

@section('content')
@php

    $sortLabels = [
        'rating' => 'По рейтингу',
        'subs'   => 'По подписчикам',
        'views'  => 'По просмотрам',
    ];
    $periodLabels = [
        'all'   => 'Всё время',
        'month' => 'Месяц',
        'week'  => 'Неделя',
    ];

    $genreVariantMap = [
        'фэнтези' => 'fantasy', 'fantasy' => 'fantasy',
        'роман' => 'romance', 'романтика' => 'romance', 'romance' => 'romance',
        'экшен' => 'action', 'боевик' => 'action', 'action' => 'action',
        'sci-fi' => 'scifi', 'фантастика' => 'scifi', 'scifi' => 'scifi',
        'мистика' => 'mystery', 'mystery' => 'mystery',
        'повседневность' => 'slice', 'slice' => 'slice',
        'хоррор' => 'horror', 'ужасы' => 'horror', 'horror' => 'horror',
        'litrpg' => 'litrpg', 'литрпг' => 'litrpg',
    ];
    $genreVar = function($name) use ($genreVariantMap) {
        $g = mb_strtolower($name);
        foreach ($genreVariantMap as $needle => $v) {
            if (mb_strpos($g, $needle) !== false) return $v;
        }
        return null;
    };

    $fmtNum = function($n) {
        if ($n >= 1_000_000) return number_format($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000)     return number_format($n / 1_000,     1) . 'K';
        return (string) (int) $n;
    };

    $metricFor = function($n) use ($sort, $fmtNum) {
        if ($sort === 'rating') return number_format((float) ($n->avg_rating ?? 0), 2);
        if ($sort === 'subs')   return $fmtNum((int) ($n->subs_count ?? 0));
        return $fmtNum((int) ($n->views_period ?? $n->views ?? 0));
    };
    $metricLabel = match($sort) {
        'rating' => 'РЕЙТИНГ',
        'subs'   => 'ПОДПИСЧИКИ',
        'views'  => 'ПРОСМОТРЫ',
    };
    $podiumLabel = match($sort) {
        'rating' => 'средний рейтинг',
        'subs'   => 'активных подписчиков',
        'views'  => 'просмотров за период',
    };
@endphp

<div class="rk-head">
    <h1>Рейтинги</h1>
    <p>Самые читаемые, самые любимые и самые подписываемые серии. Фильтруйте по периоду и метрике, чтобы посмотреть, что в моменте на верху.</p>

    <div class="rk-controls">
        
        <span class="lbl">Период</span>
        <div class="rk-pill-group">
            @foreach($periodLabels as $k => $lbl)
                <a href="{{ route('rankings', ['sort' => $sort, 'period' => $k]) }}"
                   class="{{ $period === $k ? 'on' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>

        <span class="lbl" style="margin-left:12px;">Метрика</span>
        <div class="rk-pill-group">
            @foreach($sortLabels as $k => $lbl)
                <a href="{{ route('rankings', ['sort' => $k, 'period' => $period]) }}"
                   class="{{ $sort === $k ? 'on' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>
</div>

@if($podium->count() >= 3 && $novels->currentPage() === 1)
    @php
        $gold   = $podium[0];
        $silver = $podium[1];
        $bronze = $podium[2];
        $podiumOrder = [
            ['n' => $silver, 'place' => 2, 'cls' => 'silver', 'label' => '2-е место'],
            ['n' => $gold,   'place' => 1, 'cls' => 'gold',   'label' => '★ 1-е место'],
            ['n' => $bronze, 'place' => 3, 'cls' => 'bronze', 'label' => '3-е место'],
        ];
    @endphp
    <div class="rk-podium">
        @foreach($podiumOrder as $p)
            @php $n = $p['n']; @endphp
            <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="podium-card {{ $p['cls'] }}">
                <div class="rank-num">{{ $p['place'] }}</div>
                <x-eriiba.cover :novel="$n" />
                <div class="podium-info">
                    <span class="place">{{ $p['label'] }}</span>
                    <h3>{{ $n->title }}</h3>
                    <div class="author">{{ $n->publisher->name ?? $n->author->name ?? '—' }}</div>
                    <div class="pscore">{{ $metricFor($n) }}</div>
                    <div class="pscore-lbl">{{ $podiumLabel }}</div>
                </div>
            </a>
        @endforeach
    </div>
@endif

<div class="rk-table">
    <div class="rk-row head">
        <div style="text-align:center;">#</div>
        <div></div>
        <div>Серия</div>
        <div>Главы</div>
        <div>{{ $metricLabel }}</div>
        <div>Рейтинг</div>
        <div></div>
    </div>

    @php
        $startRank = ($novels->currentPage() - 1) * $novels->perPage() + 1;
        $skipPodium = ($novels->currentPage() === 1 && $podium->count() >= 3);
    @endphp

    @foreach($novels as $i => $n)
        @php
            $rank = $startRank + $i;
            if ($skipPodium && $i < 3) continue;
            $genreObj = $n->genres->first() ?? null;
            $genreLbl = $genreObj?->name;
            $genreV   = $genreLbl ? $genreVar($genreLbl) : null;
        @endphp
        <a href="{{ route('novel.show', $n->id) }}" wire:navigate class="rk-row">
            <div class="rk-rank">{{ $rank }}<span class="small">/ {{ $novels->total() }}</span></div>
            <x-eriiba.cover :novel="$n" class="rk-cover" />
            <div class="rk-title">
                <h3>{{ $n->title }}</h3>
                <div class="author">{{ $n->publisher->name ?? $n->author->name ?? '—' }}</div>
                <div class="chips">
                    @if($genreLbl)
                        <x-eriiba.chip :variant="$genreV">{{ $genreLbl }}</x-eriiba.chip>
                    @endif
                </div>
            </div>
            <div>
                <div class="rk-num">{{ (int) $n->chapters_count }}</div>
                <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);letter-spacing:0.06em;text-transform:uppercase;font-weight:600;margin-top:4px;">глав</div>
            </div>
            <div>
                <div class="rk-num">{{ $metricFor($n) }}</div>
                <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);letter-spacing:0.06em;text-transform:uppercase;font-weight:600;margin-top:4px;">
                    {{ mb_strtolower($metricLabel) }}
                </div>
            </div>
            <div>
                <span class="rk-trend {{ ($n->avg_rating ?? 0) >= 4.5 ? 'up' : (($n->avg_rating ?? 0) >= 3.5 ? 'flat' : 'down') }}">
                    ★ {{ number_format((float) ($n->avg_rating ?? 0), 1) }}
                </span>
                <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);letter-spacing:0.04em;margin-top:4px;font-weight:600;">
                    {{ (int) ($n->subs_count ?? 0) }} ПОДП.
                </div>
            </div>
            <div class="row-act">
                <button type="button" title="К серии" onclick="event.preventDefault();window.location='{{ route('novel.show', $n->id) }}';">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </a>
    @endforeach

    @if($novels->isEmpty())
        <div style="padding:48px 0;text-align:center;font-family:var(--serif);font-size:16px;color:var(--text-3);">
            Пока нет данных для отображения.
        </div>
    @endif

    <div style="margin-top:32px;">{{ $novels->links() }}</div>
</div>
@endsection
