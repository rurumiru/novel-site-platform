<div class="editor-shell">
    
    <div class="editor-head">
        <div>
            <h1>Аналитика</h1>
            <p style="color:var(--text-muted); margin:4px 0 0; font-size:13px;">Полная статистика ваших произведений</p>
        </div>
        <div class="spacer"></div>
        <x-eriiba.btn :href="route('my-novels')" wire:navigate icon="arrow-left">Назад</x-eriiba.btn>
    </div>

    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-bottom:18px;">
        <div class="eri-tabs" style="margin-bottom:0;">
            @foreach(['7'=>'7д','30'=>'30д','90'=>'3м','365'=>'Год','730'=>'2г'] as $v => $l)
                <button type="button" wire:click="setPeriod('{{ $v }}')" class="eri-tab {{ $period === (string)$v ? 'on' : '' }}">{{ $l }}</button>
            @endforeach
        </div>
        <select wire:change="setNovelFilter($event.target.value)" class="eri-select" style="width:auto;">
            <option value="">Все новеллы</option>
            @foreach($novels as $id => $t)<option value="{{ $id }}" {{ $novelFilter == $id ? 'selected' : '' }}>{{ Str::limit($t, 30) }}</option>@endforeach
        </select>
        <button type="button" wire:click="toggleCompare" class="eri-btn sm {{ $showCompare ? 'primary' : '' }}">
            <i class="fa-solid fa-chart-column"></i> Сравнить
        </button>
    </div>

    @php
        $viewsDelta = $prevViews > 0 ? round(($totalViews - $prevViews) / $prevViews * 100) : 0;
        $revDelta = $prevRevenue > 0 ? round(($revenue - $prevRevenue) / $prevRevenue * 100) : 0;
        $cards = [
            ['Просмотры', number_format($totalViews), 'fa-eye', $viewsDelta],
            ['Сегодня', number_format($todayViews) . ' / ' . number_format($uniqueToday) . ' ун.', 'fa-calendar-day', null],
            ['Избранное', number_format($totalFavorites), 'fa-heart', null],
            ['Комментарии', number_format($totalComments) . ' (+' . $newComments . ')', 'fa-comments', null],
            ['Лайки', number_format($totalLikes), 'fa-thumbs-up', null],
            ['Доход', number_format($revenue, 0) . ' ₽', 'fa-coins', $revDelta],
        ];
    @endphp
    <div class="editor-section">
        <div class="editor-grid" style="grid-template-columns:repeat(6, 1fr); gap:10px;">
            @foreach($cards as [$label, $value, $icon, $delta])
                <div class="eri-card" style="padding:14px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span style="width:32px; height:32px; border-radius:8px; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center;">
                            <i class="fa-solid {{ $icon }}" style="font-size:12px;"></i>
                        </span>
                        @if($delta !== null && $delta !== 0)
                            <span style="font-size:10px; font-weight:700; color:{{ $delta > 0 ? 'var(--ok)' : 'var(--err)' }};">{{ $delta > 0 ? '+' : '' }}{{ $delta }}%</span>
                        @endif
                    </div>
                    <div style="font-family:var(--display); font-size:18px; font-weight:600; color:var(--text); line-height:1.2;">{{ $value }}</div>
                    <div class="eri-label" style="margin:4px 0 0;">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="editor-section">
        <div class="editor-grid" style="grid-template-columns:repeat(4, 1fr); gap:10px;">
            <div class="eri-card" style="padding:12px; display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-file-lines" style="color:var(--ok);"></i>
                <div><div style="font-weight:700; font-size:14px; color:var(--text);">{{ $totalChapters }} <span style="color:var(--ok); font-size:11px;">(+{{ $newChapters }})</span></div><div style="font-size:10px; color:var(--text-muted);">Глав опубликовано</div></div>
            </div>
            <div class="eri-card" style="padding:12px; display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-star" style="color:var(--warn);"></i>
                <div><div style="font-weight:700; font-size:14px; color:var(--text);">{{ $avgRating }} <span style="color:var(--text-muted); font-size:11px;">({{ $ratingsCount }})</span></div><div style="font-size:10px; color:var(--text-muted);">Средний рейтинг</div></div>
            </div>
            <div class="eri-card" style="padding:12px; display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-users" style="color:var(--accent);"></i>
                <div><div style="font-weight:700; font-size:14px; color:var(--text);">{{ number_format($readersCount) }}</div><div style="font-size:10px; color:var(--text-muted);">Читателей</div></div>
            </div>
            <div class="eri-card" style="padding:12px; display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-credit-card" style="color:var(--accent);"></i>
                <div><div style="font-weight:700; font-size:14px; color:var(--text);">{{ $subsActive }}</div><div style="font-size:10px; color:var(--text-muted);">Активных подписок</div></div>
            </div>
        </div>
    </div>

    <div class="editor-grid" style="grid-template-columns:2fr 1fr; gap:18px; margin-bottom:18px;">
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-chart-area" style="color:var(--accent); margin-right:6px;"></i>Просмотры</h2>
            <div style="height:280px;"><canvas id="viewsChart" wire:ignore></canvas></div>
        </section>
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-calendar-week" style="color:var(--accent); margin-right:6px;"></i>Просмотры по дням недели</h2>
            <div style="height:280px;"><canvas id="hourlyChart" wire:ignore></canvas></div>
        </section>
    </div>

    <div class="editor-grid" style="grid-template-columns:repeat(3, 1fr); gap:18px; margin-bottom:18px;">
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-comments" style="color:var(--accent); margin-right:6px;"></i>Комментарии</h2>
            <div style="height:200px;"><canvas id="commentsChart" wire:ignore></canvas></div>
        </section>
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-heart" style="color:var(--err); margin-right:6px;"></i>Избранное</h2>
            <div style="height:200px;"><canvas id="favsChart" wire:ignore></canvas></div>
        </section>
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-coins" style="color:var(--warn); margin-right:6px;"></i>Доход</h2>
            <div style="height:200px;"><canvas id="revenueChart" wire:ignore></canvas></div>
        </section>
    </div>

    <div class="editor-grid cols-2" style="margin-bottom:18px;">
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-star" style="color:var(--warn); margin-right:6px;"></i>Распределение оценок</h2>
            <div style="height:200px;"><canvas id="ratingsChart" wire:ignore></canvas></div>
        </section>
        <section class="editor-section" style="margin:0;">
            <h2><i class="fa-solid fa-mobile-screen" style="color:var(--accent); margin-right:6px;"></i>Устройства</h2>
            <div style="height:200px;"><canvas id="devicesChart" wire:ignore></canvas></div>
        </section>
    </div>

    @if($topChapters->isNotEmpty())
    <section class="editor-section" style="padding:0; overflow:hidden;">
        <h2 style="padding:18px 24px 14px; margin:0; border-bottom:1px solid var(--border);"><i class="fa-solid fa-trophy" style="color:var(--warn); margin-right:6px;"></i>Топ глав по лайкам</h2>
        <div>
            @foreach($topChapters as $i => $ch)
            <div style="display:flex; align-items:center; gap:12px; padding:12px 24px; border-top:1px solid var(--border);">
                <span style="width:24px; text-align:center; font-size:12px; font-weight:700; color:{{ $i < 3 ? 'var(--warn)' : 'var(--text-muted)' }};">{{ $i + 1 }}</span>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ch->title }}</div>
                    <div style="font-size:10px; color:var(--text-muted);">{{ $ch->novel?->title }}</div>
                </div>
                <div style="display:flex; gap:14px; font-size:12px; flex-shrink:0;">
                    <span style="color:var(--err); font-weight:600;"><i class="fa-solid fa-heart"></i> {{ $ch->likes_count }}</span>
                    <span style="color:var(--accent); font-weight:600;"><i class="fa-solid fa-comment"></i> {{ $ch->comments_count }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <section class="editor-section" style="padding:0; overflow:hidden;">
        <h2 style="padding:18px 24px 14px; margin:0; border-bottom:1px solid var(--border);"><i class="fa-solid fa-table-cells" style="color:var(--text-muted); margin-right:6px;"></i>По произведениям</h2>
        <div style="overflow-x:auto;">
            <table style="width:100%; font-size:13px; border-collapse:collapse;">
                <thead style="font-size:10px; color:var(--text-muted); text-transform:uppercase; background:var(--surface-2);">
                    <tr>
                        <th style="padding:10px 14px; text-align:left;">Новелла</th>
                        <th style="padding:10px; text-align:center;">Ст.</th>
                        <th style="padding:10px; text-align:center;">Гл.</th>
                        <th style="padding:10px; text-align:center;">Просм.</th>
                        <th style="padding:10px; text-align:center;"><i class="fa-solid fa-heart" style="color:var(--err);"></i></th>
                        <th style="padding:10px; text-align:center;"><i class="fa-solid fa-star" style="color:var(--warn);"></i></th>
                        <th style="padding:10px; text-align:center;"><i class="fa-solid fa-comment" style="color:var(--accent);"></i></th>
                        <th style="padding:10px; text-align:center;"><i class="fa-solid fa-thumbs-up" style="color:var(--err);"></i></th>
                        <th style="padding:10px 14px; text-align:right;">Доход</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($novelStats as $ns)
                    <tr style="border-top:1px solid var(--border);">
                        <td style="padding:10px 14px; font-weight:600; color:var(--text);">
                            <a href="{{ route('author.novel.stats', $ns['id']) }}" wire:navigate style="color:inherit; text-decoration:none;">{{ Str::limit($ns['title'], 35) }}</a>
                        </td>
                        <td style="padding:10px; text-align:center;">
                            @php $sm = ['ongoing'=>'В','completed'=>'З','hiatus'=>'П']; $s = $sm[$ns['status']] ?? '?'; @endphp
                            <x-eriiba.chip>{{ $s }}</x-eriiba.chip>
                        </td>
                        <td style="padding:10px; text-align:center; color:var(--text-muted); font-weight:600;">{{ $ns['chapters_count'] }}</td>
                        <td style="padding:10px; text-align:center; font-weight:700; color:var(--text);">{{ number_format($ns['views']) }}</td>
                        <td style="padding:10px; text-align:center; color:var(--err); font-weight:600;">{{ $ns['favorites'] }}</td>
                        <td style="padding:10px; text-align:center; color:var(--warn); font-weight:600;">{{ $ns['rating'] }}</td>
                        <td style="padding:10px; text-align:center; color:var(--accent); font-weight:600;">{{ $ns['comments'] }}</td>
                        <td style="padding:10px; text-align:center; color:var(--err); font-weight:600;">{{ $ns['likes'] }}</td>
                        <td style="padding:10px 14px; text-align:right; font-weight:700; color:var(--ok);">{{ number_format($ns['revenue'], 0) }} ₽</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="padding:32px 14px; text-align:center; color:var(--text-muted);">Нет данных</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    function initAllCharts() {
        var dk = document.documentElement.classList.contains('dark');
        var grid = dk ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
        var txt = dk ? '#64748b' : '#94a3b8';
        var opts = function(type) {
            return {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: type === 'pie' ? {} : {
                    x: { grid: { display: false }, ticks: { color: txt, font: { size: 9 }, maxTicksLimit: 12 } },
                    y: { grid: { color: grid }, ticks: { color: txt, font: { size: 9 } }, beginAtZero: true }
                }
            };
        };
        function make(id, cfg) {
            var el = document.getElementById(id);
            if (!el) return;
            if (el._c) el._c.destroy();
            el._c = new Chart(el, cfg);
        }

        var vd = [
            { label: 'Просмотры', data: @json($dViews), borderColor: '#6366f1', backgroundColor: dk ? 'rgba(99,102,241,0.1)' : 'rgba(99,102,241,0.15)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 },
            { label: 'Уникальные', data: @json($dUniq), borderColor: '#38bdf8', backgroundColor: 'transparent', borderDash: [4,4], tension: 0.4, borderWidth: 1.5, pointRadius: 0 }
        ];
        @if($showCompare) vd.push({ label: 'Пред. период', data: @json($dPrev), borderColor: '#94a3b8', backgroundColor: 'transparent', borderDash: [6,3], tension: 0.4, borderWidth: 1, pointRadius: 0 }); @endif
        make('viewsChart', { type: 'line', data: { labels: @json($labels), datasets: vd }, options: opts('line') });

        make('hourlyChart', { type: 'bar', data: { labels: @json($hLabels), datasets: [{ data: @json($hData), backgroundColor: dk ? 'rgba(56,189,248,0.4)' : 'rgba(56,189,248,0.6)', borderRadius: 3 }] }, options: opts('bar') });
        make('commentsChart', { type: 'line', data: { labels: @json($labels), datasets: [{ data: @json($dComments), borderColor: '#8b5cf6', backgroundColor: dk ? 'rgba(139,92,246,0.1)' : 'rgba(139,92,246,0.15)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 }] }, options: opts('line') });
        make('favsChart', { type: 'line', data: { labels: @json($labels), datasets: [{ data: @json($dFavs), borderColor: '#ec4899', backgroundColor: dk ? 'rgba(236,72,153,0.1)' : 'rgba(236,72,153,0.15)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 }] }, options: opts('line') });
        make('revenueChart', { type: 'bar', data: { labels: @json($labels), datasets: [{ data: @json($dRev), backgroundColor: dk ? 'rgba(245,158,11,0.4)' : 'rgba(245,158,11,0.6)', borderRadius: 3 }] }, options: opts('bar') });
        make('ratingsChart', { type: 'bar', data: { labels: ['1','2','3','4','5'], datasets: [{ data: @json($ratingsDistData), backgroundColor: ['#ef4444','#f97316','#eab308','#22c55e','#10b981'], borderRadius: 6 }] }, options: Object.assign({}, opts('bar'), { scales: { x: { grid: { display: false }, ticks: { color: txt, font: { size: 12, weight: 'bold' } } }, y: { grid: { color: grid }, ticks: { color: txt }, beginAtZero: true } }, indexAxis: 'y' }) });
        make('devicesChart', { type: 'doughnut', data: { labels: ['Мобильные', 'Десктоп'], datasets: [{ data: [{{ $mobile }}, {{ $desktop }}], backgroundColor: ['#6366f1', '#38bdf8'], borderWidth: 0, spacing: 2 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: txt, font: { size: 11, weight: 'bold' }, padding: 16 } } }, cutout: '65%' } });
    }
    document.addEventListener('DOMContentLoaded', initAllCharts);
    document.addEventListener('livewire:navigated', initAllCharts);
    </script>
    @endpush
</div>
