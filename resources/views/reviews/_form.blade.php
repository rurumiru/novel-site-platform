
@php
    $review = $review ?? new \App\Models\Review();
    $canPin = $canPin ?? false;
    $method = $method ?? 'POST';
@endphp

<form method="POST" action="{{ $action }}" class="review-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    @if($errors->any())
        <div class="eri-alert err" style="margin-bottom:14px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="review-form-row">
        <label class="review-form-label">Категория</label>
        <div class="review-form-radio-group">
            <label class="review-form-radio">
                <input type="radio" name="category" value="review"
                       {{ old('category', $review->category) === 'review' ? 'checked' : '' }}>
                <span>Обзор работ</span>
            </label>
            <label class="review-form-radio">
                <input type="radio" name="category" value="promotion"
                       {{ old('category', $review->category) === 'promotion' ? 'checked' : '' }}>
                <span>Продвижение работы</span>
            </label>
            @if($canPin)
                <label class="review-form-radio">
                    <input type="radio" name="category" value="notice"
                           {{ old('category', $review->category) === 'notice' ? 'checked' : '' }}>
                    <span>Уведомление (только модераторы)</span>
                </label>
            @endif
        </div>
    </div>

    <div class="review-form-row">
        <label class="review-form-label" for="title">Заголовок</label>
        <input type="text" name="title" id="title" required maxlength="200"
               value="{{ old('title', $review->title) }}"
               placeholder="Например: «Лучший роман о космосе»"
               class="review-form-input">
    </div>

    <div class="review-form-row">
        <label class="review-form-label" for="novel_id">
            Привязка к новелле <span class="review-form-hint">(необязательно — администраторы могут назначить позже)</span>
        </label>
        <input type="number" name="novel_id" id="novel_id" min="1"
               value="{{ old('novel_id', $review->novel_id) }}"
               placeholder="ID новеллы"
               class="review-form-input">
    </div>

    <div class="review-form-row">
        <label class="review-form-label" for="body">Текст</label>

        <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <script defer src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

        <input id="rv-body-input" type="hidden" name="body" value="{{ old('body', $review->body) }}">
        <div class="rv-editor-wrap">
            <trix-editor input="rv-body-input" class="rv-editor-area trix-content" placeholder="Поделитесь впечатлениями… выделите фрагмент и нажмите 🙈 чтобы скрыть его под спойлер"></trix-editor>
        </div>
        <div class="review-form-hint">
            Поддерживаются: <strong>жирный</strong>, <em>курсив</em>, <s>зачёркнутый</s>,
            ссылки, списки, заголовки, цитаты, картинки и <strong>спойлеры</strong> (кнопка 🙈 в панели — текст под спойлером откроется только по клику).
        </div>

        <script>
        (function(){
            function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
            ready(function(){
                document.addEventListener('trix-initialize', function (event) {
                    var editorEl = event.target;
                    if (editorEl.id !== '' && editorEl.dataset.rvInit) return;
                    if (editorEl.getAttribute('input') !== 'rv-body-input') return;
                    editorEl.dataset.rvInit = '1';

                    // ── Кнопка «Спойлер» ──
                    var toolbar = editorEl.previousElementSibling; // trix-toolbar
                    if (!toolbar) return;
                    var btnGroup = toolbar.querySelector('.trix-button-group--text-tools') || toolbar.querySelector('[data-trix-button-group]');
                    if (!btnGroup) return;
                    if (btnGroup.querySelector('[data-rv-spoiler]')) return;

                    var spoilerBtn = document.createElement('button');
                    spoilerBtn.type = 'button';
                    spoilerBtn.dataset.rvSpoiler = '1';
                    spoilerBtn.className = 'trix-button';
                    spoilerBtn.title = 'Скрыть под спойлер';
                    spoilerBtn.style.cssText = 'min-width:34px;font-size:14px;';
                    spoilerBtn.innerHTML = '🙈';
                    spoilerBtn.addEventListener('click', function () {
                        var editor = editorEl.editor;
                        var range = editor.getSelectedRange();
                        var sel = editor.getDocument().toString().substring(range[0], range[1]).trim();
                        var label = prompt('Текст ярлыка спойлера (по умолчанию «Спойлер»):', 'Спойлер');
                        if (label === null) return;
                        if (!label) label = 'Спойлер';
                        // Вставляем HTML-блок details/summary как HTML-фрагмент
                        var html = '<details class="rv-spoiler"><summary>' + escapeHtml(label) + '</summary>' + (sel ? escapeHtml(sel) : 'Содержимое спойлера') + '</details>';
                        editor.insertHTML(html);
                    });
                    btnGroup.appendChild(spoilerBtn);
                });

                function escapeHtml(s) {
                    return String(s).replace(/[&<>"']/g, function(c){
                        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
                    });
                }
            });
        })();
        </script>
    </div>

    @if($canPin)
        <div class="review-form-row">
            <label class="review-form-radio">
                <input type="checkbox" name="is_pinned" value="1"
                       {{ old('is_pinned', $review->is_pinned) ? 'checked' : '' }}>
                <span>Закрепить вверху списка (только для категории «Уведомление»)</span>
            </label>
        </div>
    @endif

    <div class="review-form-actions">
        <button type="submit" class="eri-btn primary">
            <i class="fa-solid fa-paper-plane"></i> Опубликовать
        </button>
        <a href="{{ route('reviews.index') }}" wire:navigate class="eri-btn">Отмена</a>
    </div>
</form>
