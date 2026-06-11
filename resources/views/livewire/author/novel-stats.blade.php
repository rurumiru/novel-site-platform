<div class="editor-shell">
    
    <div class="editor-head">
        <x-eriiba.btn :href="route('my-novels')" wire:navigate icon="arrow-left">Назад</x-eriiba.btn>
        <div style="flex:1; min-width:0;">
            <h1>Статистика</h1>
            <div style="font-family:var(--serif); font-size:15px; color:var(--text-2); margin-top:4px;">
                «{{ $novel->title }}»
            </div>
        </div>
        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="eri-btn">
            <i class="fa-solid fa-eye"></i> Открыть страницу
        </a>
    </div>

    <div class="rk-controls" style="border-bottom:none; padding-bottom:0; margin-bottom:18px;">
        <span class="lbl">Период</span>
        <div class="rk-pill-group">
            @foreach([['7','7 дней'], ['30','30 дней'], ['90','90 дней'], ['365','Год'], ['all','Всё время']] as $opt)
                <button type="button" wire:click="setPeriod('{{ $opt[0] }}')"
                        class="{{ $period === $opt[0] ? 'on' : '' }}">{{ $opt[1] }}</button>
            @endforeach
        </div>
    </div>

    <div class="profile-stats stat-grid-auto" style="margin:0 0 24px; padding:0;">
        <div class="stat-card">
            <div class="lbl">Просмотры</div>
            <div class="val">{{ number_format($totalViews, 0, '.', ' ') }}</div>
            <div class="delta {{ $viewsTrend === null ? 'neg' : ($viewsTrend >= 0 ? '' : 'neg') }}">
                @if($viewsTrend === null)
                    +{{ number_format($periodViews, 0, '.', ' ') }} за период
                @elseif($viewsTrend >= 0)
                    ▲ {{ $viewsTrend }}% к пред.
                @else
                    ▼ {{ abs($viewsTrend) }}% к пред.
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="lbl">Главы</div>
            <div class="val">{{ $publishedCount }}<span style="font-size:14px;color:var(--text-muted);"> / {{ $totalChapters }}</span></div>
            <div class="delta neg">{{ $draftCount }} черн. · {{ $lockedCount }} платн.</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Рейтинг</div>
            <div class="val">{{ number_format($avgRating, 2) }}</div>
            <div class="delta neg">{{ $ratingsCount }} оценок</div>
        </div>
        <div class="stat-card">
            <div class="lbl">В библиотеке</div>
            <div class="val">{{ number_format($totalFavorites, 0, '.', ' ') }}</div>
            <div class="delta">+{{ $periodFavs }} за период</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Подписчики</div>
            <div class="val">{{ $totalSubscribers }}</div>
            <div class="delta">+{{ $periodSubs }} за период</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Выручка</div>
            <div class="val">{{ number_format($totalRevenue, 0, '.', ' ') }}<span style="font-size:14px;color:var(--text-muted);"> ₽</span></div>
            <div class="delta neg">главы {{ number_format($unlockRevenue,0,'.',' ') }} · подп. {{ number_format($subRevenue,0,'.',' ') }}</div>
        </div>
    </div>

    @if($ratingsCount > 0)
    <div class="rating-summary" style="margin-bottom:24px;">
        <div class="rating-summary-num">
            <div class="rating-big">{{ number_format($avgRating, 1) }}</div>
            <x-eriiba.stars :value="$avgRating" :size="20" />
            <div class="rating-summary-count">{{ $ratingsCount }} оценок</div>
        </div>
        <div class="rating-summary-bars">
            @foreach($ratingDist->reverse() as $score => $count)
                @php $pct = $ratingsCount > 0 ? round($count / $ratingsCount * 100) : 0; @endphp
                <div class="rating-bar">
                    <div class="rating-bar-label">{{ $score }} ★</div>
                    <div class="rating-bar-track"><div class="rating-bar-fill" style="width:{{ $pct }}%;"></div></div>
                    <div class="rating-bar-num">{{ $count }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="ns-2col" style="display:grid; gap:18px; margin-bottom:24px;">
        <section class="editor-section">
            <h2>Просмотры</h2>
            <div class="ns-chart-box"><canvas id="chart-views" wire:ignore></canvas></div>
        </section>
        <section class="editor-section">
            <h2>Лайки глав</h2>
            <div class="ns-chart-box"><canvas id="chart-likes" wire:ignore></canvas></div>
        </section>
        <section class="editor-section">
            <h2>Комментарии</h2>
            <div class="ns-chart-box"><canvas id="chart-comments" wire:ignore></canvas></div>
        </section>
        <section class="editor-section">
            <h2>В библиотеку</h2>
            <div class="ns-chart-box"><canvas id="chart-favs" wire:ignore></canvas></div>
        </section>
        <section class="editor-section" style="grid-column:span 2;">
            <h2>Подписки</h2>
            <div class="ns-chart-box ns-chart-box-wide"><canvas id="chart-subs" wire:ignore></canvas></div>
        </section>
    </div>

    @if($retention->count() > 1)
        @php
            $maxReaders = $retention->max('readers') ?: 1;
            $firstReaders = $retention->first()->readers ?: 1;
        @endphp
        <section class="editor-section" style="margin-bottom:24px;">
            <h2>Удержание читателей</h2>
            <p style="color:var(--text-3); margin:-8px 0 16px; font-size:13px;">Сколько уникальных читателей дошло до каждой главы.</p>
            <div style="display:grid; gap:6px; max-height:480px; overflow-y:auto; padding-right:8px;">
                @foreach($retention as $r)
                    @php
                        $pct = round($r->readers / $maxReaders * 100);
                        $relPct = round($r->readers / $firstReaders * 100);
                    @endphp
                    <div style="display:grid; grid-template-columns:50px 1fr 60px 70px; gap:10px; align-items:center; font-size:12px;">
                        <span class="ch-num">#{{ $r->sort_order }}</span>
                        <div style="height:8px; background:var(--surface-2); border-radius:4px; overflow:hidden;">
                            <div style="height:100%; background:var(--accent); border-radius:4px; width:{{ $pct }}%;"></div>
                        </div>
                        <span class="ch-num" style="text-align:right;">{{ $r->readers }}</span>
                        <span class="ch-num" style="text-align:right; color:{{ $relPct >= 75 ? 'var(--ok)' : ($relPct >= 40 ? 'var(--warn)' : 'var(--err)') }};">{{ $relPct }}%</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="ns-2col" style="display:grid; gap:18px; margin-bottom:24px;">
        <section class="editor-section">
            <h2>Свежие главы</h2>
            @if($latestChapters->count() === 0)
                <p style="color:var(--text-muted); font-size:13px;">Нет данных.</p>
            @else
                <div style="display:grid; gap:6px;">
                    @foreach($latestChapters as $ch)
                        @php $when = ($ch->published_at ?? $ch->created_at)?->diffForHumans(null, true, true); @endphp
                        <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                           style="display:grid; grid-template-columns:32px 1fr auto; gap:10px; padding:8px 10px; border-radius:var(--r-sm); text-decoration:none; color:inherit; font-size:13px;"
                           onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                            <span class="ch-num">#{{ $ch->sort_order }}</span>
                            <span style="font-family:var(--serif); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ch->title }} @if(!$ch->is_published)<span style="color:var(--warn);font-size:10px;">· черн.</span>@endif</span>
                            <span class="ch-num">{{ $when }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="editor-section">
            <h2>Топ глав по лайкам</h2>
            @if($topChaptersByLikes->count() === 0)
                <p style="color:var(--text-muted); font-size:13px;">Нет данных.</p>
            @else
                <div style="display:grid; gap:6px;">
                    @foreach($topChaptersByLikes as $ch)
                        <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                           style="display:grid; grid-template-columns:32px 1fr auto; gap:10px; padding:8px 10px; border-radius:var(--r-sm); text-decoration:none; color:inherit; font-size:13px;"
                           onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                            <span class="ch-num">#{{ $ch->sort_order }}</span>
                            <span style="font-family:var(--serif); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ch->title }}</span>
                            <span class="ch-num"><i class="fa-solid fa-heart" style="font-size:10px; color:var(--err);"></i> {{ $ch->likes_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="editor-section">
            <h2>Топ глав по комментариям</h2>
            @if($topChaptersByComments->count() === 0)
                <p style="color:var(--text-muted); font-size:13px;">Нет данных.</p>
            @else
                <div style="display:grid; gap:6px;">
                    @foreach($topChaptersByComments as $ch)
                        <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                           style="display:grid; grid-template-columns:32px 1fr auto; gap:10px; padding:8px 10px; border-radius:var(--r-sm); text-decoration:none; color:inherit; font-size:13px;"
                           onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                            <span class="ch-num">#{{ $ch->sort_order }}</span>
                            <span style="font-family:var(--serif); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ch->title }}</span>
                            <span class="ch-num"><i class="fa-regular fa-comment" style="font-size:10px;"></i> {{ $ch->comments_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="editor-section">
            <h2>Выручка по главам</h2>
            @if($revenueByChapter->count() === 0)
                <p style="color:var(--text-muted); font-size:13px;">Нет покупок.</p>
            @else
                <div style="display:grid; gap:6px;">
                    @foreach($revenueByChapter as $ch)
                        <a href="{{ route('novel.read', [$novel->id, $ch->id]) }}" wire:navigate
                           style="display:grid; grid-template-columns:32px 1fr 60px 80px; gap:10px; padding:8px 10px; border-radius:var(--r-sm); text-decoration:none; color:inherit; font-size:13px;"
                           onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                            <span class="ch-num">#{{ $ch->sort_order }}</span>
                            <span style="font-family:var(--serif); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ch->title }}</span>
                            <span class="ch-num">{{ $ch->purchases }}×</span>
                            <span class="ch-num" style="color:var(--accent); font-weight:700;">{{ number_format($ch->revenue,0,'.',' ') }} ₽</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <div class="ns-2col" style="display:grid; gap:18px;">
        <section class="editor-section">
            <h2>Последние комментарии</h2>
            @if($recentComments->isEmpty())
                <p style="color:var(--text-muted); font-size:13px;">Пока нет.</p>
            @else
                <div style="display:grid; gap:12px;">
                    @foreach($recentComments as $c)
                        <div style="display:grid; grid-template-columns:32px 1fr; gap:10px; align-items:start;">
                            <div class="eri-avatar" style="width:32px; height:32px; font-size:12px;">
                                @if($c->user?->avatar_url ?? false)
                                    <img src="{{ $c->user->avatar_url }}" alt="">
                                @else
                                    {{ mb_strtoupper(mb_substr($c->user?->name ?? '?', 0, 1)) }}
                                @endif
                            </div>
                            <div style="min-width:0;">
                                <div style="font-size:12px; color:var(--text-3);">
                                    <strong style="color:var(--text);">{{ $c->user?->name ?? 'Аноним' }}</strong>
                                    · {{ $c->created_at->diffForHumans() }}
                                    @if($c->commentable_type === \App\Models\Chapter::class && $c->commentable)
                                        · <span style="font-family:var(--mono); font-size:10px;">гл. {{ $c->commentable->sort_order ?? '?' }}</span>
                                    @endif
                                </div>
                                <div style="font-family:var(--serif); font-size:13px; color:var(--text-2); margin-top:4px; max-height:60px; overflow:hidden;">
                                    {{ Str::limit(strip_tags($c->content), 180) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="editor-section">
            <h2>Новые подписчики</h2>
            @if($recentSubs->isEmpty())
                <p style="color:var(--text-muted); font-size:13px;">Пока нет.</p>
            @else
                <div style="display:grid; gap:10px;">
                    @foreach($recentSubs as $s)
                        <div style="display:grid; grid-template-columns:32px 1fr auto; gap:10px; align-items:center;">
                            <div class="eri-avatar" style="width:32px; height:32px; font-size:12px;">
                                @if($s->user?->avatar_url ?? false)
                                    <img src="{{ $s->user->avatar_url }}" alt="">
                                @else
                                    {{ mb_strtoupper(mb_substr($s->user?->name ?? '?', 0, 1)) }}
                                @endif
                            </div>
                            <div style="min-width:0;">
                                <div style="font-size:13px; color:var(--text); font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    {{ $s->user?->name ?? 'Аноним' }}
                                </div>
                                <div style="font-size:11px; color:var(--text-muted); font-family:var(--mono);">
                                    {{ $s->created_at?->diffForHumans() }}
                                    @if($s->expires_at)· до {{ $s->expires_at->format('d.m.Y') }} @endif
                                </div>
                            </div>
                            <x-eriiba.chip :variant="$s->status === 'active' ? 'accent' : null">{{ $s->status }}</x-eriiba.chip>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    function initStatsCharts() {
        if (typeof Chart === 'undefined') return setTimeout(initStatsCharts, 100);

        const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#2f6df0';
        const text3  = getComputedStyle(document.documentElement).getPropertyValue('--text-3').trim() || '#5b626d';
        const border = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || '#e3e6ec';

        const fmtDate = d => {
            const dt = new Date(d);
            return dt.getDate() + '.' + String(dt.getMonth()+1).padStart(2,'0');
        };
        const baseOpts = (color) => ({
            type: 'line',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, ticks: { color: text3, font: { size: 10 }, precision: 0 }, grid: { color: border } },
                    x: { ticks: { color: text3, font: { size: 10 }, autoSkip: true, maxRotation: 0 }, grid: { display: false } }
                },
                elements: { point: { radius: 0, hoverRadius: 4 }, line: { tension: 0.35, borderWidth: 2 } }
            },
            data: { labels: [], datasets: [{ label: '', data: [], borderColor: color, backgroundColor: color + '22', fill: true }] }
        });

        const charts = [
            ['chart-views',    @json($viewsSeries),    accent],
            ['chart-likes',    @json($likesSeries),    '#d83a3a'],
            ['chart-comments', @json($commentsSeries), '#8a6310'],
            ['chart-favs',     @json($favsSeries),     '#c43a6a'],
            ['chart-subs',     @json($subsSeries),     '#3d7a3f'],
        ];

        if (window.__novelStatsCharts) {
            Object.values(window.__novelStatsCharts).forEach(c => { try { c.destroy(); } catch(e){} });
        }
        window.__novelStatsCharts = {};

        charts.forEach(([id, series, color]) => {
            const el = document.getElementById(id);
            if (!el || !series.length) return;
            const cfg = baseOpts(color);
            cfg.data.labels = series.map(p => fmtDate(p.date));
            cfg.data.datasets[0].data = series.map(p => p.count);
            window.__novelStatsCharts[id] = new Chart(el, cfg);
        });
    }
    initStatsCharts();
    document.addEventListener('livewire:navigated', initStatsCharts);
    document.addEventListener('livewire:updated', initStatsCharts);
})();
</script>
@endpush
