@php
    $subs = \App\Models\Subscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->with(['novel', 'author'])
        ->get();
@endphp

<div class="eri-col" style="gap:24px;">
    <div>
        <h3 style="font-family:var(--display);font-size:22px;font-weight:500;letter-spacing:-0.01em;margin:0 0 14px;color:var(--text);">
            Активные подписки
        </h3>

        @if($subs->isEmpty())
            <div class="eri-card eri-card-pad-lg" style="text-align:center;padding:60px 24px;">
                <i class="fa-solid fa-crown" style="font-size:36px;color:var(--text-muted);margin-bottom:12px;"></i>
                <p style="color:var(--text-3);font-family:var(--serif);font-size:15px;margin:0;">Нет активных подписок.</p>
                <a href="{{ route('catalog') }}" wire:navigate class="eri-btn primary sm" style="margin-top:14px;">
                    Перейти в каталог
                </a>
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
                @foreach($subs as $sub)
                    <div class="eri-card" style="padding:16px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-color:var(--gold,#e0a72b);">
                        <div style="min-width:0;">
                            <div class="eri-label" style="margin:0 0 4px;color:var(--gold,#b97834);">
                                @if($sub->type === 'author_bundle')
                                    Пакет автора
                                @elseif($sub->type === 'plus')
                                    Plus
                                @else
                                    Подписка
                                @endif
                            </div>
                            @if($sub->type === 'author_bundle')
                                <div style="font-family:var(--display);font-size:16px;font-weight:500;color:var(--text);">
                                    {{ $sub->author?->name ?? '—' }}
                                </div>
                            @elseif($sub->type === 'plus')
                                <div style="font-family:var(--display);font-size:16px;font-weight:500;color:var(--text);">
                                    Глобальная подписка
                                </div>
                            @elseif($sub->novel_id)
                                <a href="{{ route('novel.show', $sub->novel_id) }}"
                                   style="font-family:var(--display);font-size:16px;font-weight:500;color:var(--text);text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $sub->novel?->title ?? '—' }}
                                </a>
                            @else
                                <div style="font-family:var(--display);font-size:16px;font-weight:500;color:var(--text-muted);">—</div>
                            @endif
                        </div>
                        <span class="eri-btn sm" style="background:color-mix(in srgb,var(--ok) 12%,var(--surface));color:var(--ok);border-color:var(--ok);pointer-events:none;">
                            <i class="fa-solid fa-circle-check"></i> Активна
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
