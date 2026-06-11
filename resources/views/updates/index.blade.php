@extends('layouts.app')
@section('title', 'Обновления')

@section('content')
<div class="eri-page">
    <section class="eri-section">
        <x-eriiba.section-head title="Последние обновления" sub="свежие главы от авторов" />

        @if($chapters->count())
            <div class="ch-table">
                @foreach($chapters as $chapter)
                    @include('partials.chapter-list-item', ['chapter' => $chapter])
                @endforeach
            </div>

            <div class="pager" style="margin-top: 28px; justify-content: center;">
                {{ $chapters->links() }}
            </div>
        @else
            <div class="lib-empty">
                Пока нет опубликованных глав. <a href="{{ route('catalog') }}" wire:navigate style="color: var(--accent); font-weight: 600;">Перейти в каталог →</a>
            </div>
        @endif
    </section>
</div>
@endsection
