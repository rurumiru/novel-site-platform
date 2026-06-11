<div style="display:flex; align-items:center; justify-content:center; padding:40px 16px;">
    <div class="eri-card eri-card-pad-lg" style="max-width:420px; width:100%; text-align:center;">
        <div style="font-size:48px; margin-bottom:16px;">🔒</div>
        <h2 style="font-family:var(--display); font-size:22px; font-weight:500; color:var(--text); margin:0 0 8px;">Глава закрыта</h2>
        <p style="color:var(--text-muted); margin:0 0 18px;">Эта глава доступна только по подписке или за монеты.</p>

        <div class="eri-card" style="padding:14px; margin-bottom:18px; background:var(--surface-2);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span style="color:var(--text-muted);">Цена главы:</span>
                @if($discount)
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="text-decoration:line-through; color:var(--text-muted); font-size:13px;">{{ $chapter->price }} ₽</span>
                        <strong style="color:var(--ok);">{{ $discountPrice }} ₽</strong>
                        <x-eriiba.chip variant="warn">−{{ $discount->promoCode->value }}%</x-eriiba.chip>
                    </div>
                @else
                    <strong style="color:var(--text);">{{ $chapter->price }} ₽</strong>
                @endif
            </div>

            @auth
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:var(--text-muted);">Ваш баланс:</span>
                    @php $effectivePrice = $discount ? $discountPrice : $chapter->price; @endphp
                    <strong style="color:{{ Auth::user()->balance >= $effectivePrice ? 'var(--ok)' : 'var(--err)' }};">
                        {{ Auth::user()->balance }} ₽
                    </strong>
                </div>
            @endauth
        </div>

        @if(session('error'))
            <div class="eri-alert err" style="margin-bottom:14px;">{{ session('error') }}</div>
        @endif

        @auth
            @if(!Auth::user()->hasVerifiedEmail())
                <p style="color:var(--warn); font-size:13px; margin-bottom:14px;">Подтвердите почту, чтобы покупать платные главы.</p>
                <x-eriiba.btn variant="primary" block :href="route('verification.notice')">Подтвердить почту</x-eriiba.btn>
            @else
                <x-eriiba.btn variant="primary" block wire:click="unlock" wire:loading.attr="disabled">
                    <span wire:loading.remove>Разблокировать за {{ $discount ? $discountPrice : $chapter->price }} ₽</span>
                    <span wire:loading>Обработка…</span>
                </x-eriiba.btn>
            @endif
        @else
            <x-eriiba.btn block :href="route('login')">Войти, чтобы купить</x-eriiba.btn>
        @endauth
    </div>
</div>
