<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Топ новелл недели</x-slot>
        <x-slot name="description">по просмотрам за 7 дней</x-slot>

        @if($novels->isEmpty())
            <div style="padding:40px 20px; text-align:center; color:var(--eri-text-muted); font-style:italic; font-family:var(--eri-serif);">
                Нет данных.
            </div>
        @else
            <div style="display:grid; gap:6px;">
                @foreach($novels as $i => $n)
                    <a href="{{ url('/admin/novels/' . $n->id . '/edit') }}"
                       style="display:grid; grid-template-columns:32px 44px 1fr auto auto; gap:12px; align-items:center;
                              padding:8px 10px; border-radius:var(--eri-r-sm);
                              text-decoration:none; color:inherit;
                              transition:background 0.12s;"
                       onmouseover="this.style.background='var(--eri-surface-2)'"
                       onmouseout="this.style.background=''">
                        <span style="font-family:var(--eri-display); font-size:18px; font-weight:600;
                                     color:{{ $i < 3 ? 'var(--eri-accent)' : 'var(--eri-text-muted)' }};
                                     text-align:center;">{{ $i + 1 }}</span>
                        <div style="width:44px; aspect-ratio:2/3; border-radius:6px; overflow:hidden;
                                    background:var(--eri-surface-3);
                                    {{ $n->cover_image ? '' : 'display:grid;place-items:center;color:var(--eri-text-muted);font-size:18px;' }}">
                            @if($n->cover_image)
                                <img src="{{ \App\Models\Novel::storageUrl($n->cover_image) }}"
                                     style="width:100%; height:100%; object-fit:cover;" alt="">
                            @else
                                <i class="fa-solid fa-book"></i>
                            @endif
                        </div>
                        <div style="min-width:0;">
                            <div style="font-family:var(--eri-display); font-size:14px; font-weight:500;
                                        color:var(--eri-text); letter-spacing:-0.01em;
                                        overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $n->title }}</div>
                            <div style="font-family:var(--eri-mono); font-size:11px; color:var(--eri-text-muted);
                                        letter-spacing:0.04em; margin-top:2px;">
                                {{ $n->chapters_count }} глав
                                @if($n->avg_rating > 0) · ★ {{ number_format((float)$n->avg_rating, 1) }} @endif
                            </div>
                        </div>
                        <span style="font-family:var(--eri-mono); font-size:11px; color:var(--eri-text-3);
                                     font-weight:600;">{{ number_format($n->views_week, 0, '.', ' ') }}</span>
                        <i class="fa-solid fa-chevron-right" style="font-size:10px; color:var(--eri-text-muted);"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
