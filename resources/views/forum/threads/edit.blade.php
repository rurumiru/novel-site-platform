@php $pageTitle = 'Редактирование — ' . $thread->title; @endphp
@extends('forum.layout')

@section('breadcrumbs')
    <span class="forum-bc-sep">/</span>
    <a href="{{ route('forum.sections.show', $section) }}">{{ $section->name }}</a>
    <span class="forum-bc-sep">/</span>
    <a href="{{ $thread->url() }}">{{ \Illuminate\Support\Str::limit($thread->title, 60) }}</a>
    <span class="forum-bc-sep">/</span>
    <span class="forum-bc-current">Редактирование</span>
@endsection

@section('forum')
    <form method="POST" action="{{ route('forum.threads.update', [$section, $thread->slug]) }}" class="forum-create">
        @csrf @method('PUT')
        <h1 class="forum-create__title">Редактирование темы</h1>

        <label class="forum-create__label">
            Заголовок
            <input type="text" name="title" class="forum-input" required maxlength="200" value="{{ old('title', $thread->title) }}">
        </label>

        <label class="forum-create__label">
            Сообщение
            <textarea name="body" class="forum-textarea" rows="10" required minlength="5" maxlength="30000">{{ old('body', $thread->body) }}</textarea>
        </label>

        <div class="forum-create__actions">
            <a href="{{ $thread->url() }}" class="forum-btn forum-btn--ghost">Отмена</a>
            <button type="submit" class="forum-btn forum-btn--primary">Сохранить</button>
        </div>
    </form>
@endsection
