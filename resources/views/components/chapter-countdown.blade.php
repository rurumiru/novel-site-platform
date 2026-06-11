@props(['date'])

@php
    $now = now();
    $target = \Carbon\Carbon::parse($date);
    if ($target->isPast()) return;
@endphp

<div x-data="{
        target: new Date('{{ $target->toIso8601String() }}').getTime(),
        totalDuration: {{ $now->diffInSeconds($target) }},
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        percent: 100,
        expired: false,
        init() {
            this.update();
            setInterval(() => this.update(), 1000);
        },
        update() {
            const now = new Date().getTime();
            const distance = this.target - now;

            if (distance <= 0) {
                this.expired = true;
                this.percent = 0;
                return;
            }

            this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
            this.percent = Math.max(0, Math.min(100, (distance / 1000) / this.totalDuration * 100));
        },
        get timeText() {
            let parts = [];
            if (this.days > 0) parts.push(this.days + 'д');
            if (this.hours > 0) parts.push(this.hours + 'ч');
            parts.push(this.minutes + 'м');
            return parts.join(' ');
        },
        get barColor() {
            if (this.percent > 60) return 'var(--ok)';
            if (this.percent > 30) return 'var(--warn)';
            return 'var(--err)';
        }
    }"
    x-show="!expired"
    x-cloak
    style="width:100%;margin-bottom:12px;user-select:none;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;">Следующая глава</span>
        <span style="font-size:11px;font-weight:700;color:var(--text-2);font-family:var(--mono);" x-text="timeText"></span>
    </div>

    <div style="position:relative;width:100%;height:6px;background:var(--surface-3);border-radius:var(--r-pill);overflow:hidden;">
        <div style="position:absolute;inset:0;height:100%;border-radius:var(--r-pill);transition:width 1s linear, background 0.3s;"
             :style="'width: ' + percent + '%; background: ' + barColor"></div>
    </div>
</div>
