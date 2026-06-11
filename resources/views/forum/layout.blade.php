
@extends('layouts.app')

@section('title', ($pageTitle ?? 'Форум') . ' · ' . \App\Models\Setting::retrieve('site_name', 'eriiba'))

@section('content')
    
    <link rel="stylesheet" href="{{ asset('css/forum.css') }}?v=1">

    <main class="forum-shell">

        <nav class="forum-breadcrumbs" aria-label="Хлебные крошки">
            <a href="{{ route('forum.index') }}" class="forum-bc-root">
                <i class="fa-solid fa-comments"></i> Форум
            </a>
            @hasSection('breadcrumbs')
                @yield('breadcrumbs')
            @endif
        </nav>

        @php($__forumHero = false)
        @if($__forumHero && !View::hasSection('hideForumHero'))
            @include('forum.partials.hero', ['initial' => $heroSnapshot ?? null])
        @endif

        @if(session('success'))
            <div class="forum-flash forum-flash--ok">
                <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="forum-flash forum-flash--err">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <div class="forum-body">
            @yield('forum')
        </div>
    </main>

    @push('scripts')
        <script src="{{ asset('js/forum-hero.js') }}?v=1" defer></script>
    @endpush
@endsection
