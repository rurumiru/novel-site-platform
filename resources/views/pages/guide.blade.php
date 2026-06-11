@extends('layouts.app')
@section('title', 'Как пользоваться сайтом')

@section('content')
<div class="eri-section guide-page"
     x-data="{
        tab: 'reader',
        open: null,
        toggle(id) { this.open = this.open === id ? null : id }
     }">

    <x-eriiba.section-head title="Как пользоваться сайтом" sub="Подробные инструкции для читателей и авторов." />

    <div class="guide-audience-tabs">
        <button type="button" @click="tab='reader'" :class="tab==='reader' ? 'on' : ''" class="guide-aud-tab">
            <i class="fa-solid fa-book-open"></i> Читателям
        </button>
        <button type="button" @click="tab='author'" :class="tab==='author' ? 'on' : ''" class="guide-aud-tab">
            <i class="fa-solid fa-pen-nib"></i> Авторам
        </button>
    </div>

    <div x-show="tab==='reader'" x-transition>
        <div class="guide-section-label">Начало работы</div>
        <div class="guide-steps">
            @php $rs = [
                ['fa-user-plus',    'Регистрация',           'Нажмите кнопку «Регистрация» в правом верхнем углу. Введите логин, email и пароль. На почту придёт письмо — подтвердите адрес, чтобы разблокировать все функции.'],
                ['fa-magnifying-glass','Поиск новелл',       'Откройте «Каталог» в верхнем меню. Используйте фильтры по жанру, тегам, статусу и году. Поиск по названию — поле в шапке сайта (⌘K).'],
                ['fa-book-open',    'Чтение',                'Нажмите на обложку новеллы → откроется страница произведения. Перейдите в нужную главу и начните читать. Прогресс сохраняется автоматически.'],
                ['fa-bookmark',     'Библиотека',            'Нажмите сердечко на странице новеллы — произведение добавится в вашу библиотеку. Перейти в библиотеку можно через иконку в шапке или через меню профиля.'],
                ['fa-bell',         'Уведомления',           'Когда выходит новая глава в произведении из вашей библиотеки — вы получите уведомление. Колокольчик в шапке показывает количество непрочитанных.'],
                ['fa-comment',      'Комментарии',           'В конце каждой главы есть раздел комментариев. Чтобы оставить комментарий, нужно быть зарегистрированным. Вы можете отвечать на комментарии других.'],
            ]; @endphp

            @foreach($rs as $i => [$icon, $title, $text])
            <div class="guide-step" @click="toggle({{ $i }})" :class="open === {{ $i }} ? 'expanded' : ''">
                <div class="guide-step-head">
                    <span class="guide-step-num">{{ $i + 1 }}</span>
                    <span class="guide-step-icon"><i class="fa-solid {{ $icon }}"></i></span>
                    <span class="guide-step-title">{{ $title }}</span>
                    <i class="fa-solid fa-chevron-down guide-step-arrow" :class="open === {{ $i }} ? 'rotated' : ''"></i>
                </div>
                <div class="guide-step-body" x-show="open === {{ $i }}" x-transition x-cloak>{{ $text }}</div>
            </div>
            @endforeach
        </div>

        <div class="guide-section-label" style="margin-top:32px;">Дополнительно</div>
        <div class="guide-cards">
            <div class="guide-card">
                <i class="fa-solid fa-star guide-card-icon"></i>
                <div>
                    <strong>Рейтинги</strong>
                    <p>В разделе «Рейтинги» — ТОП новелл по оценкам, просмотрам и числу глав. Обновляются еженедельно.</p>
                </div>
            </div>
            <div class="guide-card">
                <i class="fa-solid fa-pen guide-card-icon"></i>
                <div>
                    <strong>Обзоры</strong>
                    <p>Раздел «Обзоры» — читатели пишут рецензии на произведения. Здесь же уведомления от авторов и администрации.</p>
                </div>
            </div>
            <div class="guide-card">
                <i class="fa-solid fa-crown guide-card-icon accent"></i>
                <div>
                    <strong>Plus-подписка</strong>
                    <p>Позволяет читать авансовые главы, получить цветной ник и другие преимущества. Подробнее — в разделе «Подписка».</p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="tab==='author'" x-transition x-cloak>

        <div class="guide-section-label">Создание новеллы</div>
        <div class="guide-steps">
            @php $as1 = [
                ['fa-id-badge',     'Получить статус автора',  'На странице «Мои новеллы» заполните заявку на получение прав автора. Модератор рассмотрит её в течение суток. После одобрения вам станет доступен раздел «Мои новеллы».'],
                ['fa-folder-plus',  'Создать новеллу',         'Нажмите «+ Создать» в «Мои новеллы». Откроется редактор: введите название, описание, выберите жанры и теги. Загрузите обложку (PNG/JPEG/WebP, до 5 МБ).'],
                ['fa-image',        'Оформление',              'Помимо обложки можно загрузить фоновое изображение (отображается на странице новеллы, до 10 МБ). Добавьте теги — они помогут читателям найти произведение в каталоге.'],
                ['fa-check-circle', 'Опубликовать',            'Переключите статус новеллы в «Активна» — она появится в каталоге и поиске. Пока статус «Черновик», новелла видна только вам.'],
            ]; @endphp

            @foreach($as1 as $i => [$icon, $title, $text])
            <div class="guide-step" @click="toggle('a1-{{ $i }}')" :class="open === 'a1-{{ $i }}' ? 'expanded' : ''">
                <div class="guide-step-head">
                    <span class="guide-step-num">{{ $i + 1 }}</span>
                    <span class="guide-step-icon"><i class="fa-solid {{ $icon }}"></i></span>
                    <span class="guide-step-title">{{ $title }}</span>
                    <i class="fa-solid fa-chevron-down guide-step-arrow" :class="open === 'a1-{{ $i }}' ? 'rotated' : ''"></i>
                </div>
                <div class="guide-step-body" x-show="open === 'a1-{{ $i }}'" x-transition x-cloak>{{ $text }}</div>
            </div>
            @endforeach
        </div>

        <div class="guide-section-label" style="margin-top:32px;">Добавление глав</div>
        <div class="guide-steps">
            @php $as2 = [
                ['fa-list',         'Менеджер глав',           'В кабинете автора откройте новеллу → «Главы». Здесь отображается список всех глав. Можно менять порядок перетаскиванием, публиковать и скрывать главы.'],
                ['fa-plus',         'Новая глава',             'Нажмите «+ Добавить главу». Откроется редактор (Markdown + визуальный). Введите название и текст главы. Поддерживаются жирный, курсив, списки, цитаты, вставка изображений.'],
                ['fa-image',        'Изображения в главах',    'В редакторе главы нажмите кнопку изображения (или перетащите файл). Изображение загружается на сервер, автоматически конвертируется в WebP и вставляется в текст.'],
                ['fa-lock',         'Платные главы',           'Главу можно сделать платной: укажите стоимость (монеты) или ограничьте для авансовых читателей. Бесплатные и платные главы можно чередовать.'],
                ['fa-calendar',     'Отложенная публикация',   'Задайте дату и время публикации. Глава автоматически станет видна читателям в назначенное время — вам не нужно быть онлайн.'],
            ]; @endphp

            @foreach($as2 as $i => [$icon, $title, $text])
            <div class="guide-step" @click="toggle('a2-{{ $i }}')" :class="open === 'a2-{{ $i }}' ? 'expanded' : ''">
                <div class="guide-step-head">
                    <span class="guide-step-num">{{ $i + 1 }}</span>
                    <span class="guide-step-icon"><i class="fa-solid {{ $icon }}"></i></span>
                    <span class="guide-step-title">{{ $title }}</span>
                    <i class="fa-solid fa-chevron-down guide-step-arrow" :class="open === 'a2-{{ $i }}' ? 'rotated' : ''"></i>
                </div>
                <div class="guide-step-body" x-show="open === 'a2-{{ $i }}'" x-transition x-cloak>{{ $text }}</div>
            </div>
            @endforeach
        </div>

        <div class="guide-section-label" style="margin-top:32px;">Импорт глав из файла</div>

        <div class="guide-import-banner">
            <i class="fa-solid fa-file-import guide-import-icon"></i>
            <div>
                <strong>Поддерживаемые форматы</strong>
                <div class="guide-format-chips">
                    @foreach(['.txt', '.fb2', '.epub', '.doc', '.docx'] as $fmt)
                        <span class="guide-fmt-chip">{{ $fmt }}</span>
                    @endforeach
                </div>
                <p style="margin:8px 0 0;font-size:13px;color:var(--text-3);">Максимальный размер файла — 20 МБ.</p>
            </div>
        </div>

        <div class="guide-steps">
            @php $as3 = [
                ['fa-arrow-up-from-bracket', 'Открыть импорт',    'Перейдите в кабинет автора → ваша новелла → кнопка «Импортировать главы». Откроется страница импорта.'],
                ['fa-file',                  'Выбрать файл',       'Нажмите «Выбрать файл» или перетащите файл в область загрузки. Поддерживаются TXT, FB2, EPUB, DOC, DOCX до 20 МБ.'],
                ['fa-wand-magic-sparkles',   'Авто-разбивка',      'Система автоматически разбивает текст на главы по заголовкам (Глава 1, Chapter 1, §, и т.д.). Предпросмотр покажет, как будут разбиты главы — перед сохранением можно скорректировать.'],
                ['fa-floppy-disk',           'Сохранить',          'Нажмите «Импортировать» — главы создадутся в вашей новелле со статусом «Черновик». Потом можно редактировать и публиковать по одной или все сразу.'],
            ]; @endphp

            @foreach($as3 as $i => [$icon, $title, $text])
            <div class="guide-step" @click="toggle('a3-{{ $i }}')" :class="open === 'a3-{{ $i }}' ? 'expanded' : ''">
                <div class="guide-step-head">
                    <span class="guide-step-num">{{ $i + 1 }}</span>
                    <span class="guide-step-icon"><i class="fa-solid {{ $icon }}"></i></span>
                    <span class="guide-step-title">{{ $title }}</span>
                    <i class="fa-solid fa-chevron-down guide-step-arrow" :class="open === 'a3-{{ $i }}' ? 'rotated' : ''"></i>
                </div>
                <div class="guide-step-body" x-show="open === 'a3-{{ $i }}'" x-transition x-cloak>{{ $text }}</div>
            </div>
            @endforeach
        </div>

        <div class="guide-cards" style="margin-top:32px;">
            <div class="guide-card">
                <i class="fa-solid fa-chart-line guide-card-icon"></i>
                <div>
                    <strong>Статистика</strong>
                    <p>Раздел «Статистика» показывает просмотры, уникальных читателей и динамику по каждой новелле.</p>
                </div>
            </div>
            <div class="guide-card">
                <i class="fa-solid fa-circle-exclamation guide-card-icon warn"></i>
                <div>
                    <strong>Ошибки в тексте</strong>
                    <p>Читатели могут сообщать об ошибках. Все замечания собраны в разделе «Ошибки» кабинета автора.</p>
                </div>
            </div>
            <div class="guide-card">
                <i class="fa-solid fa-headset guide-card-icon"></i>
                <div>
                    <strong>Поддержка</strong>
                    <p>Не нашли ответ? Напишите нам в разделе «Поддержка» — ответим в течение суток.</p>
                    <a href="{{ route('support.index') }}" wire:navigate class="eri-btn sm primary" style="margin-top:10px;">
                        <i class="fa-solid fa-headset"></i> Написать
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="guide-support-cta">
        <span>Остались вопросы?</span>
        <a href="{{ route('support.index') }}" wire:navigate class="eri-btn primary sm">
            <i class="fa-solid fa-headset"></i> Написать в поддержку
        </a>
    </div>

</div>
@endsection
