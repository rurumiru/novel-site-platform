@extends('layouts.app')
@section('title', 'Новый пароль')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-eyebrow">Eriiba · Новый пароль</div>
        <h1 class="auth-h">Задайте новый пароль</h1>
        <p class="auth-sub">Придумайте пароль, который вы ещё не использовали.</p>

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label class="eri-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
                       class="eri-input">
                @error('email') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="password">Новый пароль</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" minlength="6"
                       placeholder="минимум 6 символов" class="eri-input">
                @error('password') <div class="eri-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="eri-label" for="password_confirmation">Повторите пароль</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password" minlength="6" placeholder="повторите пароль" class="eri-input">
            </div>

            @if($errors->any() && !$errors->hasAny(['email','password']))
                <div class="eri-alert err">{{ $errors->first() }}</div>
            @endif

            <x-eriiba.btn variant="primary" type="submit" size="lg" block>Сохранить пароль</x-eriiba.btn>
        </form>

        <div class="auth-foot">
            <a href="{{ route('login') }}" wire:navigate>К форме входа</a>
        </div>
    </div>
</div>
@endsection
