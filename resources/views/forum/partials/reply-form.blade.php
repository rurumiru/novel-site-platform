
<form method="POST" action="{{ route('forum.posts.store', [$section, $thread->slug]) }}" class="forum-reply" data-forum-reply>
    @csrf
    <input type="hidden" name="parent_id" value="" data-forum-parent>

    <div class="forum-reply__quote-banner" data-forum-quote-banner hidden>
        <i class="fa-solid fa-reply"></i> Ответ <span data-forum-quote-author></span>
        <button type="button" data-forum-quote-clear class="forum-link-btn">отмена</button>
    </div>

    <label class="forum-reply__label">
        <span class="forum-reply__label-title">Ваш ответ</span>
        <span class="forum-reply__hint">Поддерживается Markdown · Ctrl+Enter — отправить</span>
    </label>

    <textarea
        name="body"
        class="forum-textarea"
        rows="6"
        placeholder="Напишите ответ. Поддерживается **жирный**, *курсив*, `код`, > цитата, списки и ссылки."
        required maxlength="30000"
        data-forum-body></textarea>

    @error('body')
        <div class="forum-flash forum-flash--err">{{ $message }}</div>
    @enderror

    <div class="forum-reply__actions">
        <button type="submit" class="forum-btn forum-btn--primary">
            <i class="fa-solid fa-paper-plane"></i> Отправить
        </button>
    </div>
</form>
