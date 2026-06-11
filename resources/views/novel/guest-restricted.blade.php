@extends('layouts.app')
@section('title', 'Требуется регистрация — ' . $novel->title)

@section('content')
@php $guestLimit = (int) \App\Models\Setting::retrieve('guest_chapter_limit', 3); @endphp

<section class="eri-section" style="max-width:780px;">
    <nav class="reader-crumb" style="margin-bottom:24px;">
        <a href="{{ route('home') }}" wire:navigate>Главная</a>
        <span>›</span>
        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate>{{ $novel->title }}</a>
        <span>›</span>
        <span>{{ $chapter->title }}</span>
    </nav>

    @include('novel.partials.chapter-preview', ['chapter' => $chapter])

    <div class="im-locked" style="margin-top:0;">
        <div class="im-locked-icon"><i class="fa-solid fa-user-lock" style="color:var(--accent);"></i></div>
        <h3>Чтобы продолжить — войдите</h3>
        <p>
            Гостям доступны только первые <strong style="color:var(--text);">{{ $guestLimit }}</strong> глав для ознакомления.
            Чтобы дочитать «{{ $novel->title }}», войдите в аккаунт или зарегистрируйтесь — это бесплатно.
        </p>
        <div class="eri-row" style="justify-content:center;gap:10px;flex-wrap:wrap;margin-top:8px;">
            <x-eriiba.btn variant="primary" :href="route('register')" icon="user-plus">Зарегистрироваться</x-eriiba.btn>
            <x-eriiba.btn :href="route('login')" icon="right-to-bracket">Войти</x-eriiba.btn>
        </div>
    </div>

    <div style="margin-top:18px;text-align:center;">
        <x-eriiba.btn :href="route('novel.show', $novel->id)" variant="ghost" size="sm">
            ← Назад к новелле
        </x-eriiba.btn>
    </div>
</section>
@endsection
