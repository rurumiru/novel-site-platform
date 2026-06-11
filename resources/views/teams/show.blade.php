@extends('layouts.app')
@section('title', $team->name . ' — команда переводчиков')
@section('content')
<div class="eri-page">
    <section class="eri-section">
        
        @if($team->banner_path)
        <div style="height:200px; background:url('{{ \App\Models\Novel::storageUrl($team->banner_path) }}') center/cover; border-radius:var(--r-md); margin-bottom:-60px; position:relative;">
            <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent); border-radius:var(--r-md);"></div>
        </div>
        @endif

        <div style="display:flex; align-items:flex-end; gap:18px; margin-bottom:24px; position:relative; z-index:1;">
            @if($team->logo_path)
                <img src="{{ \App\Models\Novel::storageUrl($team->logo_path) }}"
                     style="width:80px; height:80px; border-radius:18px; object-fit:cover; border:3px solid var(--surface); box-shadow:var(--shadow-md);">
            @else
                <div style="width:80px; height:80px; border-radius:18px; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-family:var(--display); font-size:28px; font-weight:700; border:3px solid var(--surface);">
                    {{ mb_strtoupper(mb_substr($team->name, 0, 2)) }}
                </div>
            @endif
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <h1 style="font-family:var(--display); font-size:28px; font-weight:600; letter-spacing:-0.02em; margin:0; color:var(--text);">{{ $team->name }}</h1>
                    @if($team->is_official)
                        <span style="background:var(--accent); color:#fff; font-size:10px; font-weight:700; padding:4px 10px; border-radius:99px;">
                            <i class="fa-solid fa-check"></i> Официальная
                        </span>
                    @endif
                    <span style="font-size:12px; font-weight:600; color:{{ $team->status === 'recruiting' ? 'var(--ok)' : 'var(--text-muted)' }};">● {{ $team->status_label }}</span>
                </div>
                @if($team->mission)
                    <p style="font-size:14px; color:var(--text-3); margin:6px 0 0; font-family:var(--serif);">{{ $team->mission }}</p>
                @endif
            </div>
            @auth
                @if($team->canManage(Auth::id()))
                    <a href="{{ route('teams.manage', $team->slug) }}" wire:navigate class="eri-btn sm">
                        <i class="fa-solid fa-cog"></i> Управление
                    </a>
                @elseif(!$team->hasMember(Auth::id()) && $team->application_mode === 'open')
                    <a href="#apply" class="eri-btn sm primary">Подать заявку</a>
                @endif
            @endauth
        </div>

        <div style="display:flex; gap:28px; flex-wrap:wrap; padding:18px; background:var(--surface-2); border-radius:var(--r-md); margin-bottom:28px; font-size:13px; color:var(--text-3);">
            <span><strong style="color:var(--text); font-family:var(--display); font-size:20px;">{{ $team->active_members_count }}</strong><br>участников</span>
            <span><strong style="color:var(--text); font-family:var(--display); font-size:20px;">{{ $team->novels_count }}</strong><br>новелл</span>
            <span><strong style="color:var(--gold); font-family:var(--display); font-size:20px;">{{ number_format($team->total_earned, 0, '.', ' ') }}</strong><br>₽ заработано</span>
            <span><strong style="color:var(--text); font-family:var(--display); font-size:20px;">{{ $team->created_at->format('Y') }}</strong><br>год основания</span>
        </div>

        <div style="display:grid; grid-template-columns:2fr 1fr; gap:28px;">
            <div>
                
                @if($team->description)
                    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--r-md); padding:22px; margin-bottom:18px;">
                        <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 14px;">О команде</h2>
                        <div style="font-family:var(--serif); font-size:15px; line-height:1.65; color:var(--text-2);">
                            {!! nl2br(e($team->description)) !!}
                        </div>
                    </div>
                @endif

                @if($team->novels->count() > 0)
                    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--r-md); padding:22px;">
                        <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 16px;">Новеллы команды</h2>
                        <div style="display:grid; gap:12px;">
                            @foreach($team->novels as $nov)
                                <a href="{{ route('novel.show', $nov->id) }}" wire:navigate
                                   style="display:flex; gap:12px; align-items:center; text-decoration:none; padding:10px; border-radius:var(--r-sm); transition:background 0.12s;"
                                   onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
                                    <x-eriiba.cover :novel="$nov" style="width:40px; height:56px;" />
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:14px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $nov->title }}</div>
                                        <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                            @foreach($nov->genres->take(2) as $g) <span style="margin-right:6px;">{{ $g->name }}</span> @endforeach
                                        </div>
                                    </div>
                                    @if($nov->pivot->team_revenue_share > 0)
                                        <span style="font-size:11px; color:var(--accent); font-weight:600; flex-shrink:0;">{{ $nov->pivot->team_revenue_share }}%</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div>
                
                <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--r-md); padding:20px;">
                    <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 16px;">Состав</h2>
                    <div style="display:grid; gap:12px;">
                        @php $leader = $team->activeMembers->firstWhere('user_id', $team->leader_id); @endphp
                        @if($leader)
                            <div style="display:flex; align-items:center; gap:10px; padding:10px; background:color-mix(in srgb, var(--gold) 8%, var(--surface)); border-radius:var(--r-sm);">
                                <div class="eri-avatar" style="width:38px; height:38px;">
                                    @if($leader->user?->avatar_url) <img src="{{ $leader->user->avatar_url }}" alt=""> @else {{ mb_strtoupper(mb_substr($leader->user?->name ?? 'L', 0, 2)) }} @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $leader->user?->name }}</div>
                                    <div style="font-size:11px; color:var(--gold); font-weight:600;"><i class="fa-solid fa-crown"></i> Лидер</div>
                                </div>
                            </div>
                        @endif
                        @foreach($team->activeMembers->where('user_id', '!=', $team->leader_id) as $m)
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="eri-avatar" style="width:34px; height:34px; border-radius:10px;">
                                    @if($m->user?->avatar_url) <img src="{{ $m->user->avatar_url }}" alt=""> @else {{ mb_strtoupper(mb_substr($m->user?->name ?? '?', 0, 2)) }} @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px; font-weight:500; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $m->user?->name }}</div>
                                    <div style="font-size:11px; color:{{ $m->role_color }}; font-weight:600;">{{ $m->display_title }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($team->discord || $team->telegram || $team->vk || $team->website)
                    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--r-md); padding:18px; margin-top:16px;">
                        <h3 style="font-size:13px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin:0 0 12px;">Контакты</h3>
                        <div style="display:grid; gap:8px;">
                            @if($team->discord)
                                <a href="{{ $team->discord }}" target="_blank" style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text); text-decoration:none;">
                                    <i class="fa-brands fa-discord" style="color:#5865f2; width:18px;"></i> Discord
                                </a>
                            @endif
                            @if($team->telegram)
                                <a href="{{ $team->telegram }}" target="_blank" style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text); text-decoration:none;">
                                    <i class="fa-brands fa-telegram" style="color:#0088cc; width:18px;"></i> Telegram
                                </a>
                            @endif
                            @if($team->vk)
                                <a href="{{ $team->vk }}" target="_blank" style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text); text-decoration:none;">
                                    <i class="fa-brands fa-vk" style="color:#0077ff; width:18px;"></i> ВКонтакте
                                </a>
                            @endif
                            @if($team->website)
                                <a href="{{ $team->website }}" target="_blank" style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text); text-decoration:none;">
                                    <i class="fa-solid fa-globe" style="color:var(--accent); width:18px;"></i> Сайт
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
