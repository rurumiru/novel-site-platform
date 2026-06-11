<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ограничение 18+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Source+Serif+Pro:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/eriiba.css') }}">
    <link rel="stylesheet" href="{{ asset('css/eriiba-pages.css') }}">
    <style>body{background:var(--bg);color:var(--text);font-family:var(--sans);margin:0;}</style>
</head>
<body>
<div class="eri-section" style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;">
    <div style="max-width:520px;">
        <div style="font-family:var(--display);font-size:clamp(80px,14vw,140px);font-weight:500;line-height:1;letter-spacing:-0.04em;color:var(--text-faint);margin-bottom:8px;">18+</div>

        @if(isset($guest_only) && $guest_only)
            <h1 style="font-family:var(--display);font-size:30px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px;">Только для совершеннолетних</h1>
            <p style="font-family:var(--serif);font-size:16px;line-height:1.55;color:var(--text-3);margin:0 0 28px;">Войдите в свой аккаунт с указанной датой рождения не менее 18 лет.</p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px;">
                <a href="{{ route('login') }}" class="eri-btn primary lg">Войти</a>
                <a href="{{ route('register') }}" class="eri-btn lg">Регистрация</a>
                <a href="/" class="eri-btn ghost lg">На главную</a>
            </div>
        @elseif(isset($need_verify) && $need_verify)
            <h1 style="font-family:var(--display);font-size:30px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px;">Подтвердите почту</h1>
            <p style="font-family:var(--serif);font-size:16px;line-height:1.55;color:var(--text-3);margin:0 0 28px;">Подтвердите свой email — на него отправлена ссылка. Без подтверждения доступ к 18+ материалам закрыт.</p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px;">
                <a href="{{ route('verification.notice') }}" class="eri-btn primary lg">Подтвердить почту</a>
                <a href="{{ route('profile') }}" class="eri-btn lg">Профиль</a>
                <a href="/" class="eri-btn ghost lg">На главную</a>
            </div>
        @elseif(isset($need_profile) && $need_profile)
            <h1 style="font-family:var(--display);font-size:30px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px;">Заполните профиль</h1>
            <p style="font-family:var(--serif);font-size:16px;line-height:1.55;color:var(--text-3);margin:0 0 28px;">Для доступа к 18+ контенту укажите дату рождения и пол в профиле. Данные задаются один раз.</p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px;">
                <a href="{{ route('profile') }}#age-info" class="eri-btn primary lg">Заполнить профиль</a>
                <a href="/" class="eri-btn ghost lg">На главную</a>
            </div>
        @elseif(isset($too_young) && $too_young)
            <h1 style="font-family:var(--display);font-size:30px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px;">Доступ закрыт</h1>
            <p style="font-family:var(--serif);font-size:16px;line-height:1.55;color:var(--text-3);margin:0 0 28px;">Этот контент доступен пользователям от {{ $min_age ?? 18 }} лет. Согласно дате рождения в профиле, вы пока не достигли нужного возраста.</p>
            <a href="/" class="eri-btn primary lg">На главную</a>
        @else
            <h1 style="font-family:var(--display);font-size:30px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px;">Доступ закрыт</h1>
            <p style="font-family:var(--serif);font-size:16px;line-height:1.55;color:var(--text-3);margin:0 0 28px;">Контент 18+ недоступен.</p>
            <a href="/" class="eri-btn primary lg">На главную</a>
        @endif
    </div>
</div>
</body>
</html>
