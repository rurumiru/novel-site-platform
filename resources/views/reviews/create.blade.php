@extends('layouts.app')
@section('title', 'Новый обзор')

@section('content')
<div class="eri-section reviews-create">
    <x-eriiba.section-head title="Новый обзор" sub="Поделитесь рецензией или промо-постом со всем сообществом." />

    @include('reviews._form', [
        'review' => $review,
        'canPin' => $canPin,
        'action' => route('reviews.store'),
        'method' => 'POST',
    ])
</div>
@endsection
