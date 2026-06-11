<div>
    <div x-data="{ open: false }">
        <x-eriiba.btn variant="primary" icon="ticket" @click="open = !open">Промокод</x-eriiba.btn>

        <div x-show="open" x-cloak x-transition class="eri-card eri-card-pad-lg" style="margin-top:12px;">
            @if(session('promo_success'))
                <div class="eri-alert ok" style="margin-bottom:12px;">
                    <i class="fa-solid fa-circle-check"></i> {{ session('promo_success') }}
                </div>
            @endif
            @if(session('promo_error'))
                <div class="eri-alert err" style="margin-bottom:12px;">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('promo_error') }}
                </div>
            @endif

            <form wire:submit.prevent="redeem" class="editor-grid cols-2" style="gap:8px; grid-template-columns:1fr auto; align-items:end;">
                <div>
                    <label class="eri-label">Код</label>
                    <input type="text" wire:model="code" placeholder="Введите промокод" class="eri-input" style="text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">
                </div>
                <x-eriiba.btn type="submit" variant="primary" wire:loading.attr="disabled" wire:target="redeem">
                    <span wire:loading.remove wire:target="redeem">Применить</span>
                    <span wire:loading wire:target="redeem"><i class="fa-solid fa-spinner fa-spin"></i></span>
                </x-eriiba.btn>
            </form>

            @if($activeDiscounts->count() > 0)
                <div style="margin-top:14px;">
                    <div class="eri-label" style="margin-bottom:6px;">Активные скидки</div>
                    <div class="editor-grid" style="gap:6px;">
                        @foreach($activeDiscounts as $ad)
                            <div class="eri-alert warn" style="display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-tag"></i>
                                <strong>−{{ $ad->promoCode->value }}%</strong>
                                @if($ad->promoCode->novel)
                                    <span>на «{{ $ad->promoCode->novel->title }}»</span>
                                @else
                                    <span>на любую главу</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
