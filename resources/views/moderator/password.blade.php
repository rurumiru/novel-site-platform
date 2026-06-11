@extends('layouts.app')
@section('title', 'Доступ к модерации')

@section('content')
<div class="eri-section" style="max-width:480px;">
    <div class="eri-card eri-card-pad-lg" style="text-align:center;">
        <div style="width:64px;height:64px;border-radius:var(--r-md);background:var(--accent-soft);color:var(--accent);display:grid;place-items:center;margin:0 auto 18px;font-size:24px;">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h1 style="font-family:var(--display);font-size:24px;font-weight:500;letter-spacing:-0.01em;color:var(--text);margin:0 0 6px;">Панель модератора</h1>
        <p style="font-size:13px;color:var(--text-3);margin:0 0 20px;">Введите пароль для доступа</p>

        @if(session('error'))
            <div style="margin-bottom:14px;padding:10px 14px;background:var(--tag-horror-bg);color:var(--tag-horror-fg);border-radius:var(--r-sm);font-size:13px;text-align:left;">{{ session('error') }}</div>
        @endif

        <form action="{{ route('moderator.unlock') }}" method="POST">
            @csrf
            <input type="password" name="password" required autofocus placeholder="Пароль" class="eri-input" style="margin-bottom:14px;">
            <x-eriiba.btn type="submit" variant="primary" block>Войти</x-eriiba.btn>
        </form>
    </div>
</div>
@endsection
