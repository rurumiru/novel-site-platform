@props(['date', 'title' => 'До выхода новой главы:'])

<div x-data="{
        target: new Date('{{ $date->toIso8601String() }}').getTime(),
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        expired: false,
        init() {
            this.update();
            setInterval(() => this.update(), 1000);
        },
        update() {
            const now = new Date().getTime();
            const distance = this.target - now;

            if (distance < 0) {
                this.expired = true;
                return;
            }

            this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
        }
    }"
    x-show="!expired"
    x-cloak
    class="eri-card"
    style="text-align:center;padding:18px;background:var(--accent-soft);border-color:transparent;margin-bottom:16px;">

    <div style="font-size:11px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">{!! $title !!}</div>

    <div style="display:flex;justify-content:center;align-items:center;gap:12px;color:var(--text);font-family:var(--mono);">
        <div style="display:flex;flex-direction:column;align-items:center;">
            <span style="font-size:26px;font-weight:600;line-height:1;color:var(--text);" x-text="days"></span>
            <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">Дней</span>
        </div>
        <span style="font-size:22px;font-weight:600;color:var(--accent);">:</span>
        <div style="display:flex;flex-direction:column;align-items:center;">
            <span style="font-size:26px;font-weight:600;line-height:1;color:var(--text);" x-text="hours.toString().padStart(2, '0')"></span>
            <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">Часов</span>
        </div>
        <span style="font-size:22px;font-weight:600;color:var(--accent);">:</span>
        <div style="display:flex;flex-direction:column;align-items:center;">
            <span style="font-size:26px;font-weight:600;line-height:1;color:var(--text);" x-text="minutes.toString().padStart(2, '0')"></span>
            <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">Мин</span>
        </div>
        <span style="font-size:22px;font-weight:600;color:var(--accent);">:</span>
        <div style="display:flex;flex-direction:column;align-items:center;">
            <span style="font-size:26px;font-weight:600;line-height:1;color:var(--text);" x-text="seconds.toString().padStart(2, '0')"></span>
            <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">Сек</span>
        </div>
    </div>
</div>
