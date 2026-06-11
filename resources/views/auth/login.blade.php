@extends('layouts.app')
@section('title', 'Вход')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-eyebrow">Eriiba · Вход</div>
        <h1 class="auth-h">С возвращением</h1>
        <p class="auth-sub">Войдите, чтобы продолжить чтение и синхронизировать прогресс.</p>

        @if(session('status'))
            <div class="eri-alert ok" style="margin-bottom:18px;">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="field">
                <label class="eri-label" for="login">Логин или email</label>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autocomplete="username" autofocus
                       placeholder="username или email@example.com" class="eri-input">
                @error('login') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <div class="auth-row" style="margin-bottom:6px;">
                    <label class="eri-label" for="password" style="margin:0;">Пароль</label>
                    <a href="{{ route('password.request') }}">Забыли пароль?</a>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       placeholder="••••••••" class="eri-input">
                @error('password') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="auth-row">
                <label>
                    <input type="checkbox" name="remember" class="eri-checkbox">
                    <span>Запомнить меня</span>
                </label>
            </div>

            @if($errors->any() && !$errors->has('login') && !$errors->has('password'))
                <div class="eri-alert err">{{ $errors->first() }}</div>
            @endif

            <x-eriiba.btn variant="primary" type="submit" size="lg" block>Войти</x-eriiba.btn>
        </form>

        <div class="auth-foot">
            Нет аккаунта? <a href="{{ route('register') }}" wire:navigate>Создать аккаунт</a>
        </div>
    </div>
</div>
@endsection
