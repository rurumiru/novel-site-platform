@extends('layouts.app')
@section('title', 'Восстановление пароля')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-eyebrow">Eriiba · Восстановление</div>
        <h1 class="auth-h">Забыли пароль?</h1>
        <p class="auth-sub">Введите email от аккаунта — мы отправим ссылку для установки нового пароля.</p>

        @if(session('status'))
            <div class="eri-alert ok" style="margin-bottom:18px;">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="field">
                <label class="eri-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                       placeholder="email@example.com" class="eri-input">
                @error('email') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            @if($errors->any() && !$errors->has('email'))
                <div class="eri-alert err">{{ $errors->first() }}</div>
            @endif

            <x-eriiba.btn variant="primary" type="submit" size="lg" block>Отправить ссылку</x-eriiba.btn>
        </form>

        <div class="auth-foot">
            Вспомнили? <a href="{{ route('login') }}" wire:navigate>Войти</a>
            &nbsp;·&nbsp;
            <a href="{{ route('register') }}" wire:navigate>Регистрация</a>
        </div>
    </div>
</div>
@endsection
