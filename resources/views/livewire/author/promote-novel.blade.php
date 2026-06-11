<div>
    @if($activePromotions->isNotEmpty())
    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:14px;">
        @foreach($activePromotions as $promo)
        <div class="eri-alert warn" style="display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-star"></i>
            <strong>{{ $promo->package->getTypeLabel() }}</strong>
            <span style="margin-left:auto; font-size:11px;">до {{ $promo->ends_at->format('d.m.Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    @if($feedback)
    <div class="eri-alert {{ $feedbackType === 'success' ? 'ok' : 'err' }}" style="margin-bottom:12px;">
        {{ $feedback }}
    </div>
    @endif

    <x-eriiba.btn variant="primary" block wire:click="$set('modalOpen', true)" icon="rocket">Продвинуть новеллу</x-eriiba.btn>

    @if($modalOpen)
    <div style="position:fixed; inset:0; z-index:80; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.55); backdrop-filter:blur(6px); padding:16px;" wire:click.self="$set('modalOpen', false)">
        <div class="eri-card eri-card-pad-lg" style="max-width:440px; width:100%;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
                <span style="width:32px; height:32px; border-radius:8px; background:color-mix(in srgb, var(--warn) 16%, transparent); color:var(--warn); display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-rocket" style="font-size:13px;"></i>
                </span>
                <h3 style="font-family:var(--display); font-size:16px; font-weight:500; color:var(--text); margin:0;">Продвижение новеллы</h3>
                <button type="button" wire:click="$set('modalOpen', false)" style="margin-left:auto; background:none; border:none; color:var(--text-muted); cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p style="font-size:12px; color:var(--text-muted); margin:0 0 14px;">Баланс: <strong style="color:var(--text);">{{ auth()->user()->balance }} ₽</strong></p>

            @if($packages->isEmpty())
                <p style="font-size:13px; color:var(--text-muted); text-align:center; padding:18px 0;">Пакеты продвижения временно недоступны</p>
            @else
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach($packages as $pkg)
                    <div wire:click="$set('selectedPackageId', {{ $pkg->id }})"
                         class="eri-card"
                         style="padding:14px; cursor:pointer; {{ $selectedPackageId === $pkg->id ? 'border-color: var(--warn); background: color-mix(in srgb, var(--warn) 8%, transparent);' : '' }}">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                            <div>
                                <div style="font-weight:600; color:var(--text); font-size:13px;">{{ $pkg->name }}</div>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">{{ $pkg->getTypeLabel() }} · {{ $pkg->duration_days }} дней</div>
                                @if($pkg->description)
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">{{ $pkg->description }}</div>
                                @endif
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-size:18px; font-weight:700; color:var(--warn);">{{ $pkg->price_coins }}</div>
                                <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">монет</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="margin-top:18px; display:flex; gap:8px;">
                    <x-eriiba.btn block wire:click="$set('modalOpen', false)">Отмена</x-eriiba.btn>
                    <x-eriiba.btn variant="primary" block wire:click="purchase({{ $selectedPackageId ?? 0 }})" :disabled="!$selectedPackageId">Активировать</x-eriiba.btn>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
