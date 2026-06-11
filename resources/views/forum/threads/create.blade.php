@php $pageTitle = 'Новая тема — ' . $section->name; @endphp
@extends('forum.layout')

@section('breadcrumbs')
    <span class="forum-bc-sep">/</span>
    <a href="{{ route('forum.sections.show', $section) }}">{{ $section->name }}</a>
    <span class="forum-bc-sep">/</span>
    <span class="forum-bc-current">Новая тема</span>
@endsection

@section('forum')
    <form method="POST" action="{{ route('forum.threads.store', $section) }}" class="forum-create">
        @csrf

        <h1 class="forum-create__title">Новая тема</h1>

        @if($novel || $chapter)
            <div class="forum-create__context">
                @if($chapter)
                    <i class="fa-solid fa-bookmark"></i> Глава: <strong>{{ $chapter->title }}</strong>
                    @if($novel) · <i class="fa-solid fa-book"></i> {{ $novel->title }} @endif
                    <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                    @if($novel)<input type="hidden" name="novel_id" value="{{ $novel->id }}">@endif
                @elseif($novel)
                    <i class="fa-solid fa-book"></i> Новелла: <strong>{{ $novel->title }}</strong>
                    <input type="hidden" name="novel_id" value="{{ $novel->id }}">
                @endif
            </div>
        @endif

        <label class="forum-create__label">
            Заголовок
            <input type="text" name="title" class="forum-input" required maxlength="200"
                   placeholder="О чём тема?" value="{{ old('title') }}">
        </label>
        @error('title')<div class="forum-flash forum-flash--err">{{ $message }}</div>@enderror

        <label class="forum-create__label">
            Сообщение <span class="forum-reply__hint">Markdown поддерживается</span>
            <textarea name="body" class="forum-textarea" rows="10" required minlength="5" maxlength="30000"
                      placeholder="Изложите суть...">{{ old('body') }}</textarea>
        </label>
        @error('body')<div class="forum-flash forum-flash--err">{{ $message }}</div>@enderror

        <label class="forum-create__label">
            Теги <span class="forum-reply__hint">через запятую, до 8</span>
            <input type="text" name="_tags" class="forum-input" placeholder="например: rpg, перевод, обсуждение"
                   value="{{ old('_tags') }}" data-forum-tags>
            <input type="hidden" name="tags[]" data-forum-tags-target>
        </label>

        @auth
            @if(auth()->user()->hasRole(['author', 'moderator', 'deputy_admin', 'super_admin', 'owner']))
                <label class="forum-create__label">
                    Видимость
                    <select name="visibility" class="forum-input">
                        <option value="public">Публичная</option>
                        <option value="private_users">Приватная (по списку участников)</option>
                        <option value="private_roles">Только для ролей</option>
                    </select>
                </label>
            @endif
        @endauth

        <div class="forum-create__actions">
            <a href="{{ route('forum.sections.show', $section) }}" class="forum-btn forum-btn--ghost">Отмена</a>
            <button type="submit" class="forum-btn forum-btn--primary">
                <i class="fa-solid fa-paper-plane"></i> Создать тему
            </button>
        </div>
    </form>

    <script>
        // Превращаем CSV из data-forum-tags в массив тегов в hidden-input
        (function () {
            const src = document.querySelector('[data-forum-tags]');
            const dst = document.querySelector('[data-forum-tags-target]');
            const form = src?.closest('form');
            if (!form) return;
            form.addEventListener('submit', function () {
                const list = (src.value || '').split(',').map(s => s.trim()).filter(Boolean).slice(0, 8);
                // удалить старые hidden
                form.querySelectorAll('input[name="tags[]"]').forEach(el => el.remove());
                list.forEach(name => {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = 'tags[]'; i.value = name;
                    form.appendChild(i);
                });
            });
        })();
    </script>
@endsection
