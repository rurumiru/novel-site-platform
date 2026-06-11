@extends('layouts.app')
@section('title', 'Подтвердите почту')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-eyebrow">Eriiba · Подтверждение</div>
        <h1 class="auth-h">Подтвердите почту</h1>
        <p class="auth-sub">
            На <strong style="color:var(--text);font-family:var(--mono);font-size:14px;">{{ Auth::user()->email }}</strong>
            отправлена ссылка для подтверждения. Перейдите по ней, чтобы получить доступ к платным и 18+ главам.
        </p>

        @if(session('success'))
            <div class="eri-alert ok" style="margin-bottom:18px;">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
            @csrf
            <x-eriiba.btn variant="primary" type="submit" size="lg" block>
                <i class="fa-solid fa-paper-plane"></i> Отправить ссылку повторно
            </x-eriiba.btn>
        </form>

        <div class="auth-divider">или</div>

        <div class="eri-row" style="justify-content:center;gap:18px;">
            <a href="{{ route('profile') }}" wire:navigate style="color:var(--text-3);font-size:13px;font-weight:600;">
                <i class="fa-solid fa-user"></i> В профиль
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="eri-btn ghost sm">
                    <i class="fa-solid fa-right-from-bracket"></i> Выйти
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
