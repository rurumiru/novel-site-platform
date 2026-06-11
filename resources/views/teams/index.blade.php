@extends('layouts.app')
@section('title', 'Команды переводчиков')
@section('content')
<div class="eri-page">
    <section class="eri-section">
        <div class="eri-section-head">
            <h2>Команды переводчиков</h2>
            <span class="sub">{{ $teams->total() }} команд</span>
            @auth
                <a href="{{ route('teams.create') }}" wire:navigate class="eri-btn primary sm" style="margin-left:auto;">
                    <i class="fa-solid fa-plus"></i> Создать команду
                </a>
            @endauth
        </div>

        @if($teams->isEmpty())
            <div style="padding:60px; text-align:center; color:var(--text-muted); font-family:var(--serif); font-size:16px; font-style:italic;">
                Пока нет ни одной команды.
            </div>
        @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:18px;">
                @foreach($teams as $t)
                    <a href="{{ route('teams.show', $t->slug) }}" wire:navigate
                       class="team-card-link">
                        <div class="team-card-banner" style="{{ $t->banner_path ? 'background-image:url(' . \App\Models\Novel::storageUrl($t->banner_path) . ')' : '' }}">
                            @if($t->is_official)
                                <span class="team-card-official"><i class="fa-solid fa-check"></i> Официальная</span>
                            @endif
                        </div>
                        <div class="team-card-body">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                                @if($t->logo_path)
                                    <img src="{{ \App\Models\Novel::storageUrl($t->logo_path) }}" class="team-card-logo" alt="">
                                @else
                                    <div class="team-card-logo team-card-logo-placeholder">{{ mb_strtoupper(mb_substr($t->name, 0, 2)) }}</div>
                                @endif
                                <div>
                                    <div class="team-card-name">{{ $t->name }}</div>
                                    <div class="team-card-status" style="color:{{ $t->status === 'recruiting' ? 'var(--ok)' : 'var(--text-muted)' }};">
                                        ● {{ $t->status_label }}
                                    </div>
                                </div>
                            </div>
                            @if($t->mission)
                                <p class="team-card-mission">{{ Str::limit($t->mission, 90) }}</p>
                            @endif
                            <div class="team-card-meta">
                                <span><i class="fa-solid fa-users"></i> {{ $t->active_members_count ?? 0 }}</span>
                                <span><i class="fa-solid fa-book"></i> {{ $t->novels_count ?? 0 }}</span>
                                @if(($t->total_earned ?? 0) > 0)
                                    <span style="color:var(--gold);"><i class="fa-solid fa-coins"></i> {{ number_format($t->total_earned, 0, '.', ' ') }} ₽</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div style="margin-top:28px;">{{ $teams->links() }}</div>
        @endif
    </section>
</div>

<style>
.team-card-link {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}
.team-card-link:hover {
    border-color: var(--accent);
    box-shadow: 0 8px 24px rgba(47,109,240,0.12);
    transform: translateY(-2px);
}
.team-card-banner {
    height: 80px;
    background: linear-gradient(135deg, var(--accent-soft), var(--surface-2));
    background-size: cover;
    background-position: center;
    position: relative;
}
.team-card-official {
    position: absolute; top: 8px; right: 8px;
    background: var(--accent); color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 3px 9px; border-radius: 99px;
}
.team-card-body { padding: 16px; }
.team-card-logo {
    width: 48px; height: 48px; border-radius: 12px;
    object-fit: cover;
    border: 2px solid var(--border);
    flex-shrink: 0;
}
.team-card-logo-placeholder {
    display: flex; align-items: center; justify-content: center;
    background: var(--accent-soft); color: var(--accent);
    font-family: var(--display); font-size: 18px; font-weight: 700;
}
.team-card-name { font-family: var(--display); font-size: 16px; font-weight: 600; color: var(--text); }
.team-card-status { font-size: 11px; font-weight: 600; margin-top: 2px; }
.team-card-mission { font-size: 13px; color: var(--text-3); margin: 0 0 10px; font-family: var(--serif); line-height: 1.45; }
.team-card-meta { display: flex; gap: 14px; font-size: 12px; color: var(--text-muted); }
.team-card-meta i { margin-right: 4px; }
</style>
@endsection
