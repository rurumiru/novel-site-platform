<div class="eri-col" style="gap:20px;">

    <div class="eri-card eri-card-pad-lg" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div class="eri-label" style="margin:0 0 4px;">Баланс</div>
            <div style="font-family:var(--display);font-size:34px;font-weight:500;letter-spacing:-0.02em;color:var(--text);line-height:1;">
                {{ number_format((float)$user->balance, 0, ',', ' ') }} <span style="font-size:18px;color:var(--text-3);">₽</span>
            </div>
        </div>
        <div class="eri-row" style="gap:8px;flex-wrap:wrap;">
            {{-- Пополнение баланса и вывод средств отключены в публичной версии --}}
            {{-- @livewire('top-up-balance') --}}
            {{-- @livewire('request-withdrawal') --}}
            @livewire('redeem-promo-code')
        </div>
    </div>

    <div class="eri-card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 20px;border-bottom:1px solid var(--border);">
            <h3 style="font-family:var(--display);font-size:18px;font-weight:500;letter-spacing:-0.01em;margin:0;color:var(--text);">
                История операций
            </h3>
        </div>

        @php $txs = $user->transactions; @endphp

        @if($txs->isEmpty())
            <div style="padding:48px 24px;text-align:center;color:var(--text-3);font-family:var(--serif);">
                История пуста
            </div>
        @else
            
            <div class="md:hidden">
                @foreach($txs as $tx)
                    <div style="padding:14px 18px;border-top:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                        <div style="min-width:0;">
                            <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">{{ $tx->uid }}</div>
                            @if($tx->description)
                                <div style="font-size:13px;color:var(--text-2);margin-top:2px;">{{ $tx->description }}</div>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-family:var(--display);font-weight:500;font-size:16px;color:var(--text);">{{ $tx->amount }} ₽</div>
                            <div style="font-family:var(--mono);font-size:10px;letter-spacing:.06em;text-transform:uppercase;margin-top:2px;
                                color:{{ $tx->status === 'completed' ? 'var(--ok)' : ($tx->status === 'pending' ? 'var(--warn)' : 'var(--text-muted)') }};">
                                {{ $tx->status }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <table class="hidden md:table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--surface-2);">
                        <th style="text-align:left;padding:12px 20px;font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);font-weight:600;">UID</th>
                        <th style="text-align:left;padding:12px 20px;font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Описание</th>
                        <th style="text-align:left;padding:12px 20px;font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Сумма</th>
                        <th style="text-align:left;padding:12px 20px;font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Статус</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($txs as $tx)
                        <tr style="border-top:1px solid var(--border);">
                            <td style="padding:14px 20px;font-family:var(--mono);font-size:12px;color:var(--text-muted);">{{ $tx->uid }}</td>
                            <td style="padding:14px 20px;font-size:14px;color:var(--text-2);">{{ $tx->description ?? '—' }}</td>
                            <td style="padding:14px 20px;font-family:var(--display);font-weight:500;font-size:15px;color:var(--text);">{{ $tx->amount }} ₽</td>
                            <td style="padding:14px 20px;">
                                <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:var(--r-pill);font-family:var(--mono);font-size:10px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;
                                    background:{{ $tx->status === 'completed' ? 'color-mix(in srgb,var(--ok) 12%,var(--surface))' : ($tx->status === 'pending' ? 'color-mix(in srgb,var(--warn) 12%,var(--surface))' : 'var(--surface-2)') }};
                                    color:{{ $tx->status === 'completed' ? 'var(--ok)' : ($tx->status === 'pending' ? 'var(--warn)' : 'var(--text-muted)') }};">
                                    {{ $tx->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
