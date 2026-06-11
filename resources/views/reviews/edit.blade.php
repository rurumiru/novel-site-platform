@extends('layouts.app')
@section('title', 'Редактирование обзора')

@section('content')
<div class="eri-section reviews-create">
    <x-eriiba.section-head title="Редактирование" sub="Сохраните изменения, чтобы обновить обзор." />

    @include('reviews._form', [
        'review' => $review,
        'canPin' => $canPin,
        'action' => route('reviews.update', $review),
        'method' => 'PATCH',
    ])
</div>
@endsection
