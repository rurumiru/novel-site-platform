@extends('layouts.app')
@section('title', 'PLUS подписка')

@section('content')
<div class="eri-section">

    <div class="plus-hero">
        <h1>PLUS Подписка</h1>
        <p>Бесконечные истории, без ограничений. Подключите Plus и читайте сотни тысяч глав.</p>
    </div>

    @if(session('success'))
        <div class="eri-alert ok" style="margin-bottom:18px;">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="eri-alert" style="background:color-mix(in srgb, var(--warn) 10%, var(--surface));border:1px solid color-mix(in srgb, var(--warn) 30%, transparent);color:var(--text);margin-bottom:18px;">{{ session('info') }}</div>
    @endif

    @auth
        @php
            $isPlus = Auth::user()->isPlus();
            $plusUntil = Auth::user()->plusExpiresAt();
            $hasPending = Auth::user()->hasPendingPlusRequest();
        @endphp

        @if($isPlus)
            <div class="eri-card" style="margin-bottom:22px;padding:18px 22px;border-left:4px solid var(--ok, #10b981);background:color-mix(in srgb, var(--ok, #10b981) 8%, var(--surface));display:flex;gap:14px;align-items:center;">
                <i class="fa-solid fa-circle-check" style="font-size:24px;color:var(--ok, #10b981);"></i>
                <div>
                    <strong style="color:var(--text);">Plus активен.</strong>
                    @if($plusUntil)
                        <span style="color:var(--text-muted);">Действителен до {{ $plusUntil->format('d.m.Y') }}.</span>
                    @endif
                </div>
            </div>
        @elseif($hasPending)
            <div class="eri-card" style="margin-bottom:22px;padding:18px 22px;border-left:4px solid var(--warn, #d97706);background:color-mix(in srgb, var(--warn, #d97706) 8%, var(--surface));display:flex;gap:14px;align-items:center;">
                <i class="fa-solid fa-clock" style="font-size:22px;color:var(--warn, #d97706);"></i>
                <div>
                    <strong style="color:var(--text);">Заявка на Plus в обработке.</strong>
                    <span style="color:var(--text-muted);">Оплатите по реквизитам ниже. После зачисления администратор активирует подписку.</span>
                </div>
            </div>
        @endif
    @endauth

    @if($plans->isNotEmpty())
        <div class="plus-plans">
            @foreach($plans as $plan)
                @php
                    $isMonthly = $plan->key === 'monthly';
                @endphp
                <div class="plus-plan-card {{ $isMonthly ? 'recommended' : '' }}">
                    <div>
                        <h2 class="plus-plan-name">{{ $plan->name }}</h2>
                        @if($plan->short_label)
                            <div class="plus-plan-duration">{{ $plan->short_label }} · {{ $plan->duration_label }}</div>
                        @else
                            <div class="plus-plan-duration">{{ $plan->duration_label }}</div>
                        @endif
                    </div>

                    <div>
                        @if($plan->price !== null && (float)$plan->price > 0)
                            <div class="plus-plan-price">
                                {{ number_format((float)$plan->price, 0, ',', ' ') }}<span class="currency">{{ $plan->currency }}</span>
                            </div>
                            @if($plan->discount_percent > 0)
                                <div style="font-size:12px;color:var(--ok, #10b981);font-weight:600;">−{{ $plan->discount_percent }}% выгода</div>
                            @endif
                        @else
                            <div class="plus-plan-price-tbd">Цена уточняется</div>
                        @endif
                    </div>

                    @if(is_array($plan->features) && count($plan->features))
                        <ul class="plus-plan-features">
                            @foreach($plan->features as $feature)
                                <li><i class="fa-solid fa-check"></i> <span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                    @endif

                    @auth
                        @if(!Auth::user()->isPlus() && !Auth::user()->hasPendingPlusRequest())
                            <form method="POST" action="{{ route('subscription.plus', $plan) }}">
                                @csrf
                                <button type="submit" class="eri-btn {{ $isMonthly ? 'primary' : '' }} plus-plan-cta">
                                    <i class="fa-solid fa-bolt"></i> Оформить заявку
                                </button>
                            </form>
                        @else
                            <button type="button" class="eri-btn plus-plan-cta" disabled>
                                {{ Auth::user()->isPlus() ? 'Plus уже активен' : 'Заявка в обработке' }}
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="eri-btn plus-plan-cta">
                            <i class="fa-solid fa-right-to-bracket"></i> Войти, чтобы оформить
                        </a>
                    @endauth
                </div>
            @endforeach
        </div>
    @else
        <div class="eri-alert" style="margin-bottom:22px;">
            Тарифы Plus ещё не настроены. Загляните позже или свяжитесь с поддержкой.
        </div>
    @endif

    <div class="plus-payinfo">
        <h2>Как оплатить по счёту</h2>
        <p style="color:var(--text-muted);font-size:13px;line-height:1.6;margin:0 0 12px;">
            Платежи на сайте не принимаются. Оплата производится <strong>по счёту</strong> —
            переведите сумму выбранного тарифа на наши реквизиты, после чего администратор
            активирует подписку вручную в течение 24 часов.
        </p>

        <ul>
            <li>После нажатия «Оформить заявку» создаётся заявка со статусом <strong>«Ожидает подтверждения»</strong>.</li>
            <li>Свяжитесь с поддержкой <strong>https://vk.com/ru.rumi</strong> для получения реквизитов на оплату.</li>
            <li>В назначении платежа укажите ваш <strong>ID или email</strong>, чтобы мы могли найти заявку.</li>
            <li>После зачисления администратор подтвердит заявку, и Plus активируется автоматически.</li>
            <li>Действие Plus отсчитывается с момента активации администратором.</li>
            <li>Возврат средств возможен в течение 7 дней с момента активации, если функционал Plus не использовался.</li>
        </ul>
    </div>
</div>
@endsection
