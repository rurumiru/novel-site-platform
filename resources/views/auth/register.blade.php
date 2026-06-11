@extends('layouts.app')
@section('title', 'Регистрация')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-eyebrow">Eriiba · Регистрация</div>
        <h1 class="auth-h">Создать аккаунт</h1>
        <p class="auth-sub">Присоединяйтесь к сообществу читателей и авторов.</p>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="field">
                <label class="eri-label" for="name">Имя</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                       placeholder="Как к вам обращаться" class="eri-input">
                @error('name') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="username">Логин</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required
                       placeholder="my_login" class="eri-input"
                       pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30">
                <div class="eri-help">Латиница, цифры и нижнее подчёркивание. От 3 до 30 символов. Изменить нельзя.</div>
                @error('username') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                       placeholder="email@example.com" class="eri-input">
                @error('email') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="social_link">Соцсеть для связи</label>
                <input id="social_link" type="text" name="social_link" value="{{ old('social_link') }}" required
                       placeholder="https://t.me/username" class="eri-input">
                <div class="eri-help">Telegram, VK или другая ссылка — на случай если потеряете доступ к почте.</div>
                @error('social_link') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="password">Пароль</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       minlength="6" placeholder="минимум 6 символов" class="eri-input">
                @error('password') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="password_confirmation">Подтверждение пароля</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password" minlength="6" placeholder="повторите пароль" class="eri-input">
            </div>

            @if($errors->any() && !$errors->hasAny(['name','username','email','social_link','password']))
                <div class="eri-alert err">{{ $errors->first() }}</div>
            @endif

            <x-eriiba.btn variant="primary" type="submit" size="lg" block>Зарегистрироваться</x-eriiba.btn>
        </form>

        <div class="auth-foot">
            Уже есть аккаунт? <a href="{{ route('login') }}" wire:navigate>Войти</a>
        </div>
    </div>
</div>
@endsection
