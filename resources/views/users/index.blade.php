@extends('layouts.app')
@section('title', 'Пользователи')

@section('content')
<div class="eri-page">
    <section class="eri-section">
        <x-eriiba.section-head title="Пользователи" sub="авторы и читатели сообщества" />

        @if($users->count())
            <div class="users-grid">
                @foreach($users as $user)
                    @php
                        $nc = $user->novels_count;
                        $mod10 = $nc % 10; $mod100 = $nc % 100;
                        $wLabel = ($mod10 === 1 && $mod100 !== 11) ? 'работа'
                            : (($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) ? 'работы' : 'работ');
                        $isAuthor = $nc > 0;
                        $initial = mb_strtoupper(mb_substr($user->name ?? '?', 0, 1));
                    @endphp
                    <a href="{{ route('users.show', $user->id) }}" wire:navigate class="user-card">
                        <div class="av">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" loading="lazy" decoding="async">
                            @else
                                {{ $initial }}
                            @endif
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <h4>{{ $user->name }}</h4>
                            <div class="role">
                                @if($isAuthor)
                                    Автор · {{ $nc }} {{ $wLabel }}
                                @else
                                    Читатель
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pager" style="margin-top: 28px; justify-content: center;">
                {{ $users->links() }}
            </div>
        @else
            <div class="lib-empty">Пользователи не найдены.</div>
        @endif
    </section>
</div>
@endsection
