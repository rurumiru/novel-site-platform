<div>
    @if(session('donation_ok'))
        <div class="eri-alert ok" style="margin-bottom:14px;">
            <i class="fa-solid fa-heart" style="color:var(--err);"></i> {{ session('donation_ok') }}
        </div>
    @endif

    <button type="button" wire:click="$set('open', true)"
            class="eri-btn block" style="background:linear-gradient(135deg, #ff6b9d, #ff8e53); color:#fff; border:0;">
        <i class="fa-solid fa-heart"></i> Поддержать автора
    </button>

    @if($open)
        <template x-teleport="body">
        <div class="eri-modal-overlay" @click.self="$wire.set('open', false)">
            <div class="eri-card" style="width:100%; max-width:440px;" @click.stop>
                <h3 style="font-family:var(--display); font-size:20px; margin:0 0 6px;">
                    <i class="fa-solid fa-heart" style="color:#ff6b9d;"></i> Поддержать автора
                </h3>
                <p style="font-family:var(--serif); color:var(--text-2); margin:0 0 18px; font-size:14px;">
                    Кинь автору на чай. Это благодарность, не покупка глав.
                </p>

                <div class="eri-label">Сумма</div>
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:6px; margin-bottom:10px;">
                    @foreach([50, 100, 250, 500, 1000, 2500, 5000, 10000] as $a)
                        <button type="button" wire:click="setAmount({{ $a }})"
                                class="eri-btn sm {{ $amount === $a ? 'primary' : '' }}"
                                style="padding:8px 6px; font-size:12px;">
                            {{ $a }} ₽
                        </button>
                    @endforeach
                </div>
                <input type="number" wire:model.live="amount" min="10" max="100000"
                       class="eri-input" placeholder="Произвольная сумма" style="margin-bottom:14px;">
                @error('amount')<div class="eri-error" style="margin-top:-10px;margin-bottom:10px;">{{ $message }}</div>@enderror

                <div class="eri-label">Короткое сообщение (необязательно)</div>
                <textarea wire:model="message" rows="3" maxlength="280" class="eri-textarea"
                          placeholder="Спасибо за главы! Жду продолжения 💛"
                          style="margin-bottom:10px;"></textarea>
                @error('message')<div class="eri-error" style="margin-top:-6px;margin-bottom:10px;">{{ $message }}</div>@enderror

                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-2); margin-bottom:14px; cursor:pointer;">
                    <input type="checkbox" wire:model="isAnonymous" class="eri-checkbox">
                    Анонимно (имя в списке покровителей не появится)
                </label>

                @if($error)
                    <div class="eri-alert err" style="margin-bottom:14px;">{{ $error }}</div>
                @endif

                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <x-eriiba.btn variant="ghost" wire:click="$set('open', false)">Отмена</x-eriiba.btn>
                    <button type="button" wire:click="donate" wire:loading.attr="disabled"
                            class="eri-btn" style="background:linear-gradient(135deg, #ff6b9d, #ff8e53); color:#fff; border:0;">
                        <i class="fa-solid fa-heart"></i>
                        <span wire:loading.remove>Отправить {{ $amount }} ₽</span>
                        <span wire:loading><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </div>
        </div>
        </template>
    @endif
</div>
