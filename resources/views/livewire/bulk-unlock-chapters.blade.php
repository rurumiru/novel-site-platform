@php
    $count    = $chapters->count();
    $grand    = (int) $grandTotal;
    $selTotal = (int) $selectedTotal;
    $balance  = \Illuminate\Support\Facades\Auth::check() ? (int) \Illuminate\Support\Facades\Auth::user()->balance : 0;
@endphp

<div class="bulk-unlock" wire:key="bulk-unlock-{{ $novel->id }}">

    <div class="bulk-unlock-head">
        <div>
            <h2 class="display novel-h2" style="margin:0 0 4px;font-size:22px;">
                <i class="fa-solid fa-cart-shopping" style="margin-right:8px;color:var(--accent);"></i>Купить главы массово
            </h2>
            <p style="margin:0;color:var(--text-3);font-size:13px;">
                Выберите главы поштучно или возьмите все разом — сумма пересчитывается автоматически.
            </p>
        </div>
        @auth
            <div class="bulk-unlock-balance">
                <span style="color:var(--text-muted);font-size:12px;">Баланс</span>
                <strong style="color:{{ $balance >= $selTotal ? 'var(--ok)' : 'var(--text)' }};">{{ number_format($balance) }} ₽</strong>
            </div>
        @endauth
    </div>

    @if($count === 0)
        <div class="eri-alert" style="margin:14px 0 0;">
            <i class="fa-solid fa-circle-check" style="margin-right:6px;color:var(--ok);"></i>
            @auth
                У этой новеллы нет платных глав, доступных для покупки, либо все уже открыты.
            @else
                В этой новелле сейчас нет платных глав для покупки.
            @endauth
        </div>
    @else
        @if($error)
            <div class="eri-alert err" style="margin:14px 0 0;">{{ $error }}</div>
        @endif
        @if($success)
            <div class="eri-alert" style="margin:14px 0 0;background:var(--ok-bg, rgba(0,180,90,0.1));color:var(--ok);">
                <i class="fa-solid fa-check" style="margin-right:6px;"></i>{{ $success }}
            </div>
        @endif

        <div class="bulk-unlock-controls">
            <button type="button" class="eri-btn ghost sm" wire:click="selectAll" wire:loading.attr="disabled">
                <i class="fa-regular fa-square-check" style="margin-right:6px;"></i>Выбрать все ({{ $count }})
            </button>
            <button type="button" class="eri-btn ghost sm" wire:click="clearSelection" wire:loading.attr="disabled" @if(empty($selected)) disabled @endif>
                <i class="fa-regular fa-square" style="margin-right:6px;"></i>Очистить
            </button>
            <div class="bulk-unlock-spacer"></div>
            <div class="bulk-unlock-summary">
                <span class="bulk-unlock-summary-label">Выбрано</span>
                <strong class="bulk-unlock-summary-num">{{ count($selected) }}</strong>
                <span class="bulk-unlock-summary-sep">·</span>
                <strong class="bulk-unlock-summary-sum">{{ number_format($selTotal) }} ₽</strong>
            </div>
        </div>

        <div class="bulk-unlock-list">
            @foreach($chapters as $ch)
                <label class="bulk-unlock-row">
                    <input type="checkbox" wire:model.live="selected" value="{{ $ch->id }}">
                    <span class="bulk-unlock-num">#{{ $ch->sort_order }}</span>
                    <span class="bulk-unlock-title">{{ $ch->title }}</span>
                    <span class="bulk-unlock-price">{{ $ch->price }} ₽</span>
                </label>
            @endforeach
        </div>

        <div class="bulk-unlock-actions">
            @auth
                <button type="button" class="eri-btn primary"
                        wire:click="buy"
                        wire:loading.attr="disabled"
                        @if(empty($selected) || $selTotal <= 0) disabled @endif>
                    <span wire:loading.remove wire:target="buy">
                        <i class="fa-solid fa-bolt" style="margin-right:6px;"></i>
                        Купить выбранные · {{ number_format($selTotal) }} ₽
                    </span>
                    <span wire:loading wire:target="buy">Обработка…</span>
                </button>
                <button type="button" class="eri-btn"
                        wire:click="selectAll"
                        wire:loading.attr="disabled"
                        title="Выбрать все, чтобы потом нажать «Купить выбранные»">
                    <i class="fa-solid fa-layer-group" style="margin-right:6px;"></i>
                    Все сразу · {{ number_format($grand) }} ₽
                </button>
            @else
                <a href="{{ route('login') }}" class="eri-btn primary" wire:navigate>
                    <i class="fa-solid fa-right-to-bracket" style="margin-right:6px;"></i>
                    Войдите, чтобы купить
                </a>
                <span style="color:var(--text-muted);font-size:13px;align-self:center;">
                    Всего: <strong>{{ number_format($grand) }} ₽</strong> за {{ $count }} {{ \Illuminate\Support\Str::plural('главу', $count) }}
                </span>
            @endauth
        </div>
    @endif
</div>
