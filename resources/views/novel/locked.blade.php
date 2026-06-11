@extends('layouts.app')
@section('title', 'Глава закрыта — ' . $novel->title)

@section('content')

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
        <div class="im-locked-icon"><i class="fa-solid fa-lock" style="color:var(--warn);"></i></div>
        <h3>Эта глава платная</h3>
        <p>
            Чтобы прочитать главу полностью, разблокируйте её разово
            @if($chapter->price > 0) за <strong style="color:var(--text);">{{ $chapter->price }} ₽</strong> @endif
            или оформите подписку на новеллу.
        </p>
    </div>

    <div class="eri-card" style="margin-top:18px;">
        @livewire('unlock-chapter', ['chapter' => $chapter])
    </div>

    <div style="margin-top:18px;text-align:center;">
        <x-eriiba.btn :href="route('novel.show', $novel->id)" variant="ghost" size="sm">
            ← Назад к новелле
        </x-eriiba.btn>
    </div>
</section>
@endsection
