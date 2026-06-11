@extends('layouts.app')
@section('title', 'Команды переводчиков — eriiba')
@section('meta_description', 'Экосистема для команд переводчиков: гибкие роли, прозрачные доли дохода, инструменты совместной работы и аналитика.')

@section('content')
<div class="eri-page teams-soon-page">

    <section class="tsoon-hero">
        <div class="tsoon-blob tsoon-blob-1"></div>
        <div class="tsoon-blob tsoon-blob-2"></div>
        <div class="tsoon-blob tsoon-blob-3"></div>

        <div class="tsoon-hero-inner">
            <div class="tsoon-badge">
                <i class="fa-solid fa-flask"></i>
                Beta · в закрытом тестировании
            </div>
            <h1 class="tsoon-title">Команды<br>переводчиков</h1>
            <p class="tsoon-lead">
                Полноценная среда для совместной работы: гибкие роли,
                прозрачные доли дохода, общий глоссарий, внутренние обсуждения
                и подробная аналитика — на одной платформе.
            </p>
            <div class="tsoon-hero-cta">
                <a href="#mockups" class="eri-btn primary">
                    <i class="fa-solid fa-grip-lines"></i>
                    Посмотреть макеты
                </a>
                <a href="#join" class="eri-btn">
                    <i class="fa-regular fa-bell"></i>
                    Подписаться на запуск
                </a>
            </div>
        </div>
    </section>

    <section class="tsoon-section">
        <div class="tsoon-pillars">
            @foreach([
                ['fa-people-group', 'Совместная работа',  'Один профиль — много новелл, много участников, чёткие зоны ответственности.'],
                ['fa-chart-pie',    'Честные доли',       'Распределение дохода настраивается отдельно на каждую новеллу и фиксируется журналом.'],
                ['fa-language',     'Единый стандарт',    'Общие глоссарии имён и терминов синхронизируются между всеми главами и переводчиками.'],
                ['fa-chart-line',   'Прозрачная аналитика','Дашборд видят все участники: просмотры, удержание, доход — без скрытых данных.'],
            ] as [$ic, $t, $d])
                <div class="tsoon-pillar">
                    <div class="tsoon-pillar-icon"><i class="fa-solid {{ $ic }}"></i></div>
                    <div class="tsoon-pillar-title">{{ $t }}</div>
                    <div class="tsoon-pillar-desc">{{ $d }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="tsoon-section" id="mockups">
        <div class="tsoon-section-head">
            <div class="tsoon-section-label">Превью интерфейсов</div>
            <h2 class="tsoon-section-title">Как это будет выглядеть</h2>
            <p class="tsoon-section-sub">Все макеты ниже — реальные дизайны будущих экранов команд на eriiba.</p>
        </div>
    </section>

    <section class="tsoon-mockup-section">
        <div class="tsoon-mockup-intro">
            <div class="tsoon-section-label">Макет №1</div>
            <h3 class="tsoon-mockup-title">Публичный профиль команды</h3>
            <p class="tsoon-mockup-desc">
                Лендинг команды с биографией, портфолио новелл, составом и формой
                подачи заявки на вступление. Виден всем, индексируется поисковиками.
            </p>
            <ul class="tsoon-mockup-bullets">
                <li><i class="fa-solid fa-check"></i> Обложка и логотип</li>
                <li><i class="fa-solid fa-check"></i> Описание, специализация, языки</li>
                <li><i class="fa-solid fa-check"></i> Все новеллы под одной крышей</li>
                <li><i class="fa-solid fa-check"></i> Состав с ролями и аватарками</li>
                <li><i class="fa-solid fa-check"></i> Заявка / приглашение / открытый набор</li>
            </ul>
        </div>

        <div class="tsoon-browser">
            <div class="tsoon-browser-bar">
                <span class="tsoon-dot tsoon-dot-r"></span>
                <span class="tsoon-dot tsoon-dot-y"></span>
                <span class="tsoon-dot tsoon-dot-g"></span>
                <div class="tsoon-browser-url">eriiba.ru/teams/lunar-translators</div>
            </div>
            <div class="tsoon-browser-body">

                <div class="mk-team-cover" style="--c:#ec4899;">
                    <div class="mk-team-avatar"><i class="fa-solid fa-moon"></i></div>
                    <div class="mk-team-head">
                        <div class="mk-team-name">Лунные переводчики</div>
                        <div class="mk-team-tag">Японские романтические новеллы · с весны</div>
                        <div class="mk-team-meta">
                            <span><i class="fa-solid fa-book"></i> Несколько активных тайтлов</span>
                            <span><i class="fa-solid fa-users"></i> Небольшая команда</span>
                            <span><i class="fa-solid fa-star"></i> Высокие оценки</span>
                        </div>
                    </div>
                    <div class="mk-team-actions">
                        <button class="eri-btn primary sm" disabled><i class="fa-solid fa-door-open"></i> Подать заявку</button>
                        <button class="eri-btn sm" disabled><i class="fa-regular fa-heart"></i> Подписаться</button>
                    </div>
                </div>

                <div class="mk-tabs">
                    <span class="mk-tab on">Новеллы</span>
                    <span class="mk-tab">Состав</span>
                    <span class="mk-tab">Расписание</span>
                    <span class="mk-tab">Вакансии <em>•</em></span>
                    <span class="mk-tab">О команде</span>
                </div>

                <div class="mk-novels-grid">
                    @foreach(['#a78bfa','#f472b6','#34d399','#fbbf24'] as $i => $c)
                        <div class="mk-novel">
                            <div class="mk-novel-cover" style="background:linear-gradient(135deg,{{ $c }},color-mix(in srgb,{{ $c }} 40%,#1a1a1a))">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div class="mk-novel-info">
                                <div class="mk-novel-title">Название новеллы {{ $i + 1 }}</div>
                                <div class="mk-novel-sub">Переводчик · Редактор · Бета</div>
                                <div class="mk-novel-progress">
                                    <div class="mk-novel-bar"><div style="width:{{ [78, 55, 92, 34][$i] }}%"></div></div>
                                    <span>в работе</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mk-members">
                    <div class="mk-block-title">Состав</div>
                    <div class="mk-members-row">
                        @foreach([
                            ['#ef4444','fa-crown','Лидер'],
                            ['#10b981','fa-language','Переводчик · JP'],
                            ['#10b981','fa-language','Переводчик · JP'],
                            ['#3b82f6','fa-pen-nib','Редактор'],
                            ['#8b5cf6','fa-layer-group','Тайпсеттер'],
                            ['#6b7280','fa-eye','Бета'],
                        ] as [$col, $ic, $role])
                            <div class="mk-member">
                                <div class="mk-member-ava" style="--c:{{ $col }};"><i class="fa-solid {{ $ic }}"></i></div>
                                <div class="mk-member-name">Никнейм</div>
                                <div class="mk-member-role">{{ $role }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="tsoon-mockup-section reverse">
        <div class="tsoon-mockup-intro">
            <div class="tsoon-section-label">Макет №2</div>
            <h3 class="tsoon-mockup-title">Профиль участника</h3>
            <p class="tsoon-mockup-desc">
                Каждый участник видит, в каких командах и тайтлах он состоит,
                какую роль занимает и в каких долях участвует — на одном экране.
                Все изменения логируются и могут быть оспорены через арбитра.
            </p>
            <ul class="tsoon-mockup-bullets">
                <li><i class="fa-solid fa-check"></i> Список команд и ролей</li>
                <li><i class="fa-solid fa-check"></i> Доля по каждому тайтлу отдельно</li>
                <li><i class="fa-solid fa-check"></i> История изменений долей</li>
                <li><i class="fa-solid fa-check"></i> Договор и подпись (если включено)</li>
            </ul>
        </div>

        <div class="tsoon-browser">
            <div class="tsoon-browser-bar">
                <span class="tsoon-dot tsoon-dot-r"></span>
                <span class="tsoon-dot tsoon-dot-y"></span>
                <span class="tsoon-dot tsoon-dot-g"></span>
                <div class="tsoon-browser-url">eriiba.ru/me/team-shares</div>
            </div>
            <div class="tsoon-browser-body">

                <div class="mk-profile-head">
                    <div class="mk-profile-ava">A</div>
                    <div>
                        <div class="mk-profile-name">Aleksei · @nick</div>
                        <div class="mk-profile-sub">Состоит в нескольких командах · переводчик / редактор</div>
                    </div>
                </div>

                <div class="mk-block-title">Мои команды</div>
                <div class="mk-myteams">
                    @foreach([
                        ['#ec4899','fa-moon','Лунные переводчики','Переводчик JP→RU','Активный участник'],
                        ['#10b981','fa-snowflake','Северная лига','Редактор','Гость на проекте'],
                    ] as [$c, $ic, $name, $role, $status])
                        <div class="mk-myteam">
                            <div class="mk-myteam-logo" style="--c:{{ $c }};"><i class="fa-solid {{ $ic }}"></i></div>
                            <div class="mk-myteam-info">
                                <div class="mk-myteam-name">{{ $name }}</div>
                                <div class="mk-myteam-role">{{ $role }} · {{ $status }}</div>
                            </div>
                            <div class="mk-myteam-cta">Открыть</div>
                        </div>
                    @endforeach
                </div>

                <div class="mk-block-title">Распределение по тайтлам</div>
                <div class="mk-shares">

                    @foreach([
                        ['Название новеллы A', 65, '#6366f1'],
                        ['Название новеллы B', 30, '#10b981'],
                        ['Название новеллы C', 15, '#f59e0b'],
                    ] as [$n, $w, $col])
                        <div class="mk-share-row">
                            <div class="mk-share-name">{{ $n }}</div>
                            <div class="mk-share-bar">
                                <div class="mk-share-fill" style="width:{{ $w }}%; background:{{ $col }};"></div>
                                <span class="mk-share-pct">ваша доля</span>
                            </div>
                            <div class="mk-share-meta">переводчик</div>
                        </div>
                    @endforeach

                </div>

                <div class="mk-log">
                    <div class="mk-block-title">История изменений</div>
                    @foreach([
                        ['fa-arrow-up',   '#10b981', 'Доля по тайтлу A увеличена',  'координатор — несколько дней назад'],
                        ['fa-user-plus',  '#6366f1', 'Назначены редактором',         'лидер команды — на прошлой неделе'],
                        ['fa-handshake',  '#f59e0b', 'Принята заявка в команду',     'лидер команды — пара недель назад'],
                    ] as [$ic, $col, $title, $sub])
                        <div class="mk-log-row">
                            <div class="mk-log-icon" style="--c:{{ $col }};"><i class="fa-solid {{ $ic }}"></i></div>
                            <div>
                                <div class="mk-log-title">{{ $title }}</div>
                                <div class="mk-log-sub">{{ $sub }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    <section class="tsoon-mockup-section">
        <div class="tsoon-mockup-intro">
            <div class="tsoon-section-label">Макет №3</div>
            <h3 class="tsoon-mockup-title">Финансовый дашборд</h3>
            <p class="tsoon-mockup-desc">
                Общий дашборд команды показывает доход в реальном времени,
                разбивку по тайтлам, по участникам, графики динамики
                и таблицу запланированных выплат. Открыт всем участникам.
            </p>
            <ul class="tsoon-mockup-bullets">
                <li><i class="fa-solid fa-check"></i> График дохода день/неделя/месяц</li>
                <li><i class="fa-solid fa-check"></i> Топ-новелл по выручке</li>
                <li><i class="fa-solid fa-check"></i> Список выплат с датами</li>
                <li><i class="fa-solid fa-check"></i> Экспорт для бухгалтерии</li>
            </ul>
        </div>

        <div class="tsoon-browser">
            <div class="tsoon-browser-bar">
                <span class="tsoon-dot tsoon-dot-r"></span>
                <span class="tsoon-dot tsoon-dot-y"></span>
                <span class="tsoon-dot tsoon-dot-g"></span>
                <div class="tsoon-browser-url">eriiba.ru/teams/lunar/finance</div>
            </div>
            <div class="tsoon-browser-body">

                <div class="mk-fin-summary">
                    @foreach([
                        ['fa-coins',           'Доход за период', '#6366f1', 'на нужном уровне'],
                        ['fa-arrow-trend-up',  'Динамика',        '#10b981', 'выше прошлой недели'],
                        ['fa-users',           'Активных получателей', '#f59e0b', 'все участники'],
                        ['fa-calendar-check',  'Ближайшая выплата', '#ec4899', 'на этой неделе'],
                    ] as [$ic, $lbl, $col, $v])
                        <div class="mk-fin-card" style="--c:{{ $col }};">
                            <div class="mk-fin-icon"><i class="fa-solid {{ $ic }}"></i></div>
                            <div class="mk-fin-lbl">{{ $lbl }}</div>
                            <div class="mk-fin-val">{{ $v }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mk-fin-chart">
                    <div class="mk-block-title small">Поступления по дням</div>
                    <svg viewBox="0 0 600 160" preserveAspectRatio="none" class="mk-svg">
                        <defs>
                            <linearGradient id="g1" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%"   stop-color="var(--accent)" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,120 L40,100 L80,110 L120,80 L160,95 L200,60 L240,75 L280,50 L320,70 L360,40 L400,55 L440,30 L480,50 L520,25 L560,40 L600,20 L600,160 L0,160 Z" fill="url(#g1)"/>
                        <path d="M0,120 L40,100 L80,110 L120,80 L160,95 L200,60 L240,75 L280,50 L320,70 L360,40 L400,55 L440,30 L480,50 L520,25 L560,40 L600,20" stroke="var(--accent)" stroke-width="2" fill="none"/>
                    </svg>
                </div>

                <div class="mk-fin-bottom">
                    <div class="mk-fin-titles">
                        <div class="mk-block-title small">Топ-новеллы команды</div>
                        <div class="mk-fin-list">
                            @foreach(['Название A', 'Название B', 'Название C', 'Название D'] as $i => $title)
                                <div class="mk-fin-list-row">
                                    <span class="mk-fin-list-rank">{{ $i+1 }}</span>
                                    <span class="mk-fin-list-name">{{ $title }}</span>
                                    <div class="mk-fin-list-bar"><div style="width:{{ [88, 64, 47, 25][$i] }}%; background:linear-gradient(90deg,var(--accent),color-mix(in srgb,var(--accent) 60%, #8b5cf6))"></div></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mk-fin-titles">
                        <div class="mk-block-title small">Запланированные выплаты</div>
                        <div class="mk-fin-payout">
                            @foreach([
                                ['Никнейм1', 'переводчик', 'на ближайшие выплаты'],
                                ['Никнейм2', 'редактор',   'на ближайшие выплаты'],
                                ['Никнейм3', 'тайпсеттер', 'после публикации'],
                            ] as [$nick, $role, $when])
                                <div class="mk-payout-row">
                                    <div class="mk-payout-name">{{ $nick }} <span>({{ $role }})</span></div>
                                    <div class="mk-payout-when">{{ $when }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="tsoon-mockup-section reverse">
        <div class="tsoon-mockup-intro">
            <div class="tsoon-section-label">Макет №4</div>
            <h3 class="tsoon-mockup-title">Глоссарий и стайл-гайд</h3>
            <p class="tsoon-mockup-desc">
                Общий словарь имён, мест, терминов и форм обращения.
                Меняется в одном месте — обновляется во всех главах команды.
                У переводчика подсказки прямо в редакторе главы.
            </p>
            <ul class="tsoon-mockup-bullets">
                <li><i class="fa-solid fa-check"></i> Имена с вариантами и пол</li>
                <li><i class="fa-solid fa-check"></i> Термины и аббревиатуры</li>
                <li><i class="fa-solid fa-check"></i> Форма обращения (-сан / -кун / на «ты»)</li>
                <li><i class="fa-solid fa-check"></i> Правила транслитерации</li>
            </ul>
        </div>

        <div class="tsoon-browser">
            <div class="tsoon-browser-bar">
                <span class="tsoon-dot tsoon-dot-r"></span>
                <span class="tsoon-dot tsoon-dot-y"></span>
                <span class="tsoon-dot tsoon-dot-g"></span>
                <div class="tsoon-browser-url">eriiba.ru/teams/lunar/glossary</div>
            </div>
            <div class="tsoon-browser-body">
                <div class="mk-glossary-head">
                    <div class="mk-search-fake">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Поиск по глоссарию…</span>
                    </div>
                    <div class="mk-glossary-tabs">
                        <span class="mk-tab on">Имена</span>
                        <span class="mk-tab">Места</span>
                        <span class="mk-tab">Термины</span>
                        <span class="mk-tab">Стиль</span>
                    </div>
                </div>

                <table class="mk-glossary-table">
                    <thead>
                        <tr>
                            <th>Оригинал</th>
                            <th>Перевод</th>
                            <th>Тип</th>
                            <th>Заметка</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['ナタリー',  'Натали',     'женское имя', 'обращение: «-сан»'],
                            ['ヴィクトル','Виктор',    'мужское имя', 'кличка: «генерал»'],
                            ['北の塔',     'Северная Башня','место',  'крепость на севере'],
                            ['結界',       'барьер магии', 'термин', 'не «защитный купол»'],
                            ['師匠',       'учитель',     'обращение', 'не «мастер»'],
                        ] as [$orig, $tr, $type, $note])
                            <tr>
                                <td><span class="mk-glossary-orig">{{ $orig }}</span></td>
                                <td><strong>{{ $tr }}</strong></td>
                                <td><span class="mk-glossary-pill">{{ $type }}</span></td>
                                <td><span class="mk-glossary-note">{{ $note }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="tsoon-mockup-section">
        <div class="tsoon-mockup-intro">
            <div class="tsoon-section-label">Макет №5</div>
            <h3 class="tsoon-mockup-title">Чат и заметки по тайтлам</h3>
            <p class="tsoon-mockup-desc">
                Внутренние треды обсуждений по каждой новелле. Упоминания,
                реакции, прикрепление черновиков. Доступ только участникам команды.
            </p>
            <ul class="tsoon-mockup-bullets">
                <li><i class="fa-solid fa-check"></i> Треды по новелле и по главе</li>
                <li><i class="fa-solid fa-check"></i> Упоминания @username</li>
                <li><i class="fa-solid fa-check"></i> Прикрепление файлов и черновиков</li>
                <li><i class="fa-solid fa-check"></i> Подсветка непрочитанного</li>
            </ul>
        </div>

        <div class="tsoon-browser">
            <div class="tsoon-browser-bar">
                <span class="tsoon-dot tsoon-dot-r"></span>
                <span class="tsoon-dot tsoon-dot-y"></span>
                <span class="tsoon-dot tsoon-dot-g"></span>
                <div class="tsoon-browser-url">eriiba.ru/teams/lunar/chat</div>
            </div>
            <div class="tsoon-browser-body">
                <div class="mk-chat">

                    <aside class="mk-chat-side">
                        <div class="mk-chat-side-title">Каналы</div>
                        <div class="mk-chat-channel on"># общий</div>
                        <div class="mk-chat-channel"># новелла А <em>•</em></div>
                        <div class="mk-chat-channel"># новелла Б</div>
                        <div class="mk-chat-channel"># глоссарий</div>
                        <div class="mk-chat-channel">🔒 редакция</div>
                    </aside>

                    <div class="mk-chat-main">
                        <div class="mk-chat-msg">
                            <div class="mk-chat-ava" style="--c:#ec4899;">Л</div>
                            <div>
                                <div class="mk-chat-meta"><b>Лидер</b> <span>сегодня</span></div>
                                <div class="mk-chat-text">Завтра разбираем главу — кто возьмёт правку имени персонажа?</div>
                            </div>
                        </div>
                        <div class="mk-chat-msg">
                            <div class="mk-chat-ava" style="--c:#10b981;">П</div>
                            <div>
                                <div class="mk-chat-meta"><b>Переводчик</b> <span>сегодня</span></div>
                                <div class="mk-chat-text">Возьму. <span class="mk-chat-mention">@Редактор</span>, посмотри потом стилистику?</div>
                                <div class="mk-chat-react">
                                    <span>👍 2</span><span>🔥 1</span>
                                </div>
                            </div>
                        </div>
                        <div class="mk-chat-msg">
                            <div class="mk-chat-ava" style="--c:#3b82f6;">Р</div>
                            <div>
                                <div class="mk-chat-meta"><b>Редактор</b> <span>несколько минут назад</span></div>
                                <div class="mk-chat-text">Окей, после обеда займусь. И добавил пометку в глоссарий.</div>
                                <div class="mk-chat-attach">
                                    <i class="fa-solid fa-paperclip"></i> глава-черновик.docx
                                </div>
                            </div>
                        </div>
                        <div class="mk-chat-input">
                            <i class="fa-regular fa-face-smile"></i>
                            <span>Написать в #новелла А…</span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="tsoon-section">
        <div class="tsoon-section-head">
            <div class="tsoon-section-label">Роли</div>
            <h2 class="tsoon-section-title">Восемь ролей на любую задачу</h2>
            <p class="tsoon-section-sub">У каждой роли свои права и зоны ответственности. Один человек может совмещать несколько.</p>
        </div>

        <div class="tsoon-roles-grid">
            @foreach([
                ['fa-crown',             '#ef4444', 'Лидер',          'Управляет составом, ролями и долями. Получает закреплённую за организатором долю.'],
                ['fa-sitemap',           '#f59e0b', 'Координатор',    'Распределяет главы, ведёт расписание, контролирует сроки и качество.'],
                ['fa-language',          '#10b981', 'Переводчик',     'Переводит главы. Может работать с несколькими тайтлами, доли — раздельно.'],
                ['fa-pen-nib',           '#3b82f6', 'Редактор',       'Стилистическая правка, единый голос текста, проверка по глоссарию.'],
                ['fa-layer-group',       '#8b5cf6', 'Тайпсеттер',     'Вёрстка глав, расстановка иллюстраций, спойлеры и форматирование.'],
                ['fa-palette',           '#ec4899', 'Иллюстратор',    'Обложки, превью, баннеры — за фикс или долю с тайтла.'],
                ['fa-magnifying-glass',  '#6366f1', 'TLC (контроль)', 'Сверка с оригиналом, ловит смысловые ошибки и пропуски.'],
                ['fa-eye',               '#6b7280', 'Бета-ридер',     'Читает черновики до публикации, даёт обратную связь и ловит опечатки.'],
            ] as [$icon, $color, $name, $desc])
                <div class="tsoon-role-card" style="--c: {{ $color }};">
                    <div class="tsoon-role-icon"><i class="fa-solid {{ $icon }}"></i></div>
                    <div class="tsoon-role-name">{{ $name }}</div>
                    <div class="tsoon-role-desc">{{ $desc }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="tsoon-section">
        <div class="tsoon-section-head">
            <div class="tsoon-section-label">Как это работает</div>
            <h2 class="tsoon-section-title">От создания до первой выплаты</h2>
        </div>

        <div class="tsoon-flow">
            @foreach([
                ['01', 'Создаёте команду',     'Название, описание, логотип, специализация, языки и жанры. Профиль становится публичным.'],
                ['02', 'Набираете участников', 'Через прямые приглашения или открытые заявки. Каждому — роль и доля.'],
                ['03', 'Привязываете новеллы', 'Свои переводы или новые тайтлы. Доли по умолчанию или индивидуально по тайтлу.'],
                ['04', 'Получаете выплаты',    'Доход с подписок и premium-глав распределяется автоматически и переводится регулярно.'],
            ] as [$num, $title, $desc])
                <div class="tsoon-flow-step">
                    <div class="tsoon-flow-num">{{ $num }}</div>
                    <div class="tsoon-flow-content">
                        <div class="tsoon-flow-title">{{ $title }}</div>
                        <div class="tsoon-flow-desc">{{ $desc }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="tsoon-section">
        <div class="tsoon-section-head">
            <div class="tsoon-section-label">FAQ</div>
            <h2 class="tsoon-section-title">Частые вопросы</h2>
        </div>

        <div class="tsoon-faq" x-data="{ open: 0 }">
            @foreach([
                ['Сколько стоит создание команды?',
                 'Создание и базовое использование — бесплатно. Платформа удерживает небольшую долю от выручки команды на инфраструктуру и развитие. Никаких подписок «за создание команды».'],
                ['Можно ли быть в нескольких командах?',
                 'Да. Один аккаунт может состоять в любом количестве команд с разными ролями и долями. Все начисления приходят в один кошелёк.'],
                ['Что если переводчик ушёл с уже опубликованной новеллой?',
                 'Каждая раскладка долей привязана к версии записи. Уход участника не отнимает у него выплаты по уже опубликованным главам — они сохраняются на исторических ставках. Новые главы — по новой раскладке.'],
                ['Как защищена интеллектуальная собственность?',
                 'У каждой команды приватный архив черновиков и переписки. На коммерческие тайтлы можно подключить NDA-режим: переписка шифруется, доступ к источникам логируется.'],
                ['Можно ли привлекать собственных меценатов?',
                 'Да. Любая команда может подключить свой Boosty / Patreon / ЮMoney как дополнительный канал. Доходы оттуда не учитываются нашей комиссией — это ваше внешнее.'],
                ['Что с эксклюзивами и Plus-подпиской?',
                 'Команды смогут отмечать главы как Plus-эксклюзив. Выплата за просмотр такой главы выше, чем за обычный premium.'],
            ] as $i => [$q, $a])
                <div class="tsoon-faq-item" :class="open === {{ $i }} ? 'is-open' : ''">
                    <button type="button" class="tsoon-faq-q" @click="open = (open === {{ $i }} ? -1 : {{ $i }})">
                        <span>{{ $q }}</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="tsoon-faq-a" x-show="open === {{ $i }}" x-collapse>
                        <p>{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="tsoon-cta" id="join">
        <div class="tsoon-cta-inner">
            <h2 class="tsoon-cta-title">Готовы запустить свою команду?</h2>
            <p class="tsoon-cta-sub">
                Открытое тестирование стартует скоро. Подпишитесь —<br>
                пришлём приглашение в числе первых.
            </p>

            <form class="tsoon-cta-form"
                  onsubmit="event.preventDefault(); this.querySelector('button').innerHTML='<i class=\'fa-solid fa-check\'></i> Готово, напишем!'; this.querySelector('button').disabled=true;">
                <input type="email" placeholder="Ваш e-mail" required>
                <button type="submit" class="eri-btn primary lg">
                    <i class="fa-regular fa-bell"></i> Подписаться на запуск
                </button>
            </form>

            <p class="tsoon-cta-fine">Мы не шлём спам. Только запуск и один ежемесячный дайджест.</p>
        </div>
    </section>

</div>

<style>
.teams-soon-page { padding-bottom: 100px; }

/* ──────────────────────────────────────────────
   COMMON
   ────────────────────────────────────────────── */
.tsoon-section {
    max-width: 1100px;
    margin: 0 auto;
    padding: 64px 24px;
}
.tsoon-section-head { text-align: center; margin-bottom: 40px; }
.tsoon-section-label {
    font-family: var(--mono, monospace);
    font-size: 11px; font-weight: 700;
    letter-spacing: .14em; text-transform: uppercase;
    color: var(--accent); margin-bottom: 10px;
}
.tsoon-section-title {
    font-family: var(--display);
    font-size: clamp(26px, 3.5vw, 38px);
    font-weight: 500; line-height: 1.15;
    color: var(--text); margin: 0 0 12px;
    letter-spacing: -0.02em;
}
.tsoon-section-sub {
    font-size: 15px; color: var(--text-muted);
    max-width: 640px; margin: 0 auto; line-height: 1.6;
}
.mk-block-title {
    font-family: var(--mono, monospace);
    font-size: 11px; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--text-muted); margin: 18px 0 10px;
}
.mk-block-title.small { font-size: 10px; }

/* ──────────────────────────────────────────────
   HERO
   ────────────────────────────────────────────── */
.tsoon-hero {
    position: relative; overflow: hidden;
    padding: clamp(70px, 11vw, 110px) 24px clamp(60px, 9vw, 90px);
    text-align: center;
    border-bottom: 1px solid var(--border);
    margin-bottom: 24px;
}
.tsoon-blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: .18; pointer-events: none; }
.tsoon-blob-1 { width: 480px; height: 480px; background: var(--accent); top: -160px; left: -120px; animation: tsblob 14s ease-in-out infinite alternate; }
.tsoon-blob-2 { width: 360px; height: 360px; background: #10b981; bottom: -100px; right: -80px; animation: tsblob 18s ease-in-out infinite alternate-reverse; }
.tsoon-blob-3 { width: 240px; height: 240px; background: #f59e0b; top: 40%; left: 55%; animation: tsblob 22s ease-in-out infinite alternate; }
@keyframes tsblob {
    0%   { transform: scale(1) translate(0, 0); }
    50%  { transform: scale(1.12) translate(30px, -20px); }
    100% { transform: scale(0.95) translate(-20px, 30px); }
}
.tsoon-hero-inner { position: relative; z-index: 1; max-width: 720px; margin: 0 auto; }
.tsoon-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--surface); border: 1.5px solid var(--border); border-radius: 999px;
    padding: 5px 14px 5px 10px; font-size: 12px; font-weight: 700;
    color: var(--text-muted); letter-spacing: .06em; text-transform: uppercase;
    margin-bottom: 28px;
}
.tsoon-badge i { color: var(--accent); font-size: 11px; }
.tsoon-title {
    font-family: var(--display); font-size: clamp(40px, 6.5vw, 72px); font-weight: 600;
    line-height: 1.05; color: var(--text); margin: 0 0 22px; letter-spacing: -0.025em;
}
.tsoon-lead {
    font-size: clamp(15px, 1.9vw, 18px); color: var(--text-muted);
    line-height: 1.7; max-width: 620px; margin: 0 auto;
}
.tsoon-hero-cta {
    display: flex; gap: 12px; justify-content: center;
    margin: 32px 0 0; flex-wrap: wrap;
}

/* ──────────────────────────────────────────────
   PILLARS
   ────────────────────────────────────────────── */
.tsoon-pillars {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
}
@media (max-width: 900px) { .tsoon-pillars { grid-template-columns: 1fr 1fr; } }
@media (max-width: 540px) { .tsoon-pillars { grid-template-columns: 1fr; } }
.tsoon-pillar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 16px; padding: 24px 22px;
    text-align: center; transition: border-color .2s, transform .2s;
}
.tsoon-pillar:hover { border-color: var(--accent); transform: translateY(-3px); }
.tsoon-pillar-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: color-mix(in srgb, var(--accent) 12%, transparent);
    color: var(--accent); display: grid; place-items: center;
    font-size: 22px; margin: 0 auto 14px;
}
.tsoon-pillar-title { font-family: var(--display); font-size: 17px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
.tsoon-pillar-desc  { font-size: 13px; color: var(--text-muted); line-height: 1.55; }

/* ──────────────────────────────────────────────
   MOCKUPS — SECTION LAYOUT
   ────────────────────────────────────────────── */
.tsoon-mockup-section {
    max-width: 1180px; margin: 80px auto; padding: 0 24px;
    display: grid; grid-template-columns: 340px 1fr; gap: 48px;
    align-items: center;
}
.tsoon-mockup-section.reverse { grid-template-columns: 1fr 340px; }
.tsoon-mockup-section.reverse .tsoon-mockup-intro { order: 2; }
.tsoon-mockup-section.reverse .tsoon-browser { order: 1; }
@media (max-width: 980px) {
    .tsoon-mockup-section,
    .tsoon-mockup-section.reverse { grid-template-columns: 1fr; gap: 32px; }
    .tsoon-mockup-section.reverse .tsoon-mockup-intro,
    .tsoon-mockup-section.reverse .tsoon-browser { order: initial; }
}
.tsoon-mockup-intro .tsoon-section-label { text-align: left; margin-bottom: 12px; }
.tsoon-mockup-title { font-family: var(--display); font-size: clamp(22px, 2.6vw, 28px); font-weight: 600; line-height: 1.15; color: var(--text); margin: 0 0 12px; letter-spacing: -0.015em; }
.tsoon-mockup-desc  { font-size: 14px; color: var(--text-muted); line-height: 1.65; margin-bottom: 18px; }
.tsoon-mockup-bullets { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
.tsoon-mockup-bullets li { font-size: 13px; color: var(--text-2); display: flex; gap: 8px; align-items: center; }
.tsoon-mockup-bullets li i { color: var(--ok, #10b981); font-size: 10px; flex-shrink: 0; }

/* Browser-frame */
.tsoon-browser {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 14px 40px rgba(15,20,25,0.08), 0 2px 6px rgba(15,20,25,0.04);
    min-width: 0;
}
.tsoon-browser-bar {
    display: flex; align-items: center; gap: 6px;
    padding: 10px 14px;
    background: var(--surface-2); border-bottom: 1px solid var(--border);
}
.tsoon-dot { width: 10px; height: 10px; border-radius: 50%; }
.tsoon-dot-r { background: #ef4444; }
.tsoon-dot-y { background: #f59e0b; }
.tsoon-dot-g { background: #10b981; }
.tsoon-browser-url {
    flex: 1; text-align: center; font-family: var(--mono, monospace);
    font-size: 11.5px; color: var(--text-muted);
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 6px; padding: 4px 10px;
    margin-left: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.tsoon-browser-body { padding: 22px; }

/* ──────────────────────────────────────────────
   MOCKUP 1 — TEAM PAGE
   ────────────────────────────────────────────── */
.mk-team-cover {
    display: grid; grid-template-columns: 84px 1fr auto; gap: 18px; align-items: center;
    padding: 18px; border-radius: 12px;
    background: linear-gradient(135deg, color-mix(in srgb, var(--c) 30%, var(--surface)), var(--surface));
    border: 1px solid color-mix(in srgb, var(--c) 30%, var(--border));
}
.mk-team-avatar {
    width: 84px; height: 84px; border-radius: 18px;
    background: var(--c); color: white; display: grid; place-items: center;
    font-size: 36px; box-shadow: 0 6px 18px color-mix(in srgb, var(--c) 40%, transparent);
}
.mk-team-name { font-family: var(--display); font-size: 22px; font-weight: 600; color: var(--text); }
.mk-team-tag  { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
.mk-team-meta { display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap; font-size: 11.5px; color: var(--text-3); }
.mk-team-meta i { color: var(--c); margin-right: 4px; }
.mk-team-actions { display: flex; gap: 8px; flex-direction: column; }
.mk-team-actions .eri-btn { opacity: 0.85; cursor: not-allowed; }
@media (max-width: 600px) {
    .mk-team-cover { grid-template-columns: 64px 1fr; }
    .mk-team-actions { grid-column: 1 / -1; flex-direction: row; }
    .mk-team-avatar { width: 64px; height: 64px; font-size: 28px; }
    .mk-team-name { font-size: 18px; }
}

.mk-tabs { display: flex; gap: 0; margin: 18px 0 14px; border-bottom: 1px solid var(--border); flex-wrap: wrap; }
.mk-tab {
    padding: 10px 14px; font-size: 12.5px; font-weight: 600;
    color: var(--text-muted); border-bottom: 2px solid transparent;
    margin-bottom: -1px; white-space: nowrap;
}
.mk-tab.on { color: var(--accent); border-color: var(--accent); }
.mk-tab em { color: var(--err, #ef4444); font-style: normal; margin-left: 4px; }

.mk-novels-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
}
@media (max-width: 720px) { .mk-novels-grid { grid-template-columns: 1fr; } }
.mk-novel {
    display: flex; gap: 12px;
    padding: 12px; background: var(--surface-2); border-radius: 10px;
}
.mk-novel-cover {
    width: 56px; height: 80px; border-radius: 6px;
    display: grid; place-items: center; color: white; flex: none;
    font-size: 20px;
}
.mk-novel-title { font-size: 13.5px; font-weight: 600; color: var(--text); line-height: 1.3; }
.mk-novel-sub   { font-size: 11px; color: var(--text-muted); margin: 4px 0 6px; }
.mk-novel-progress { display: flex; gap: 8px; align-items: center; }
.mk-novel-bar { flex: 1; height: 5px; background: var(--surface-3, var(--border)); border-radius: 99px; overflow: hidden; }
.mk-novel-bar > div { height: 100%; background: linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--accent) 60%, #10b981)); border-radius: 99px; }
.mk-novel-progress span { font-size: 10.5px; color: var(--text-muted); }

.mk-members-row {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px;
}
@media (max-width: 760px) { .mk-members-row { grid-template-columns: repeat(3, 1fr); } }
.mk-member { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; }
.mk-member-ava {
    width: 40px; height: 40px; border-radius: 50%;
    background: color-mix(in srgb, var(--c) 18%, var(--surface));
    color: var(--c); display: grid; place-items: center; font-size: 15px;
    border: 1.5px solid color-mix(in srgb, var(--c) 35%, var(--border));
}
.mk-member-name { font-size: 11.5px; font-weight: 600; color: var(--text); }
.mk-member-role { font-size: 10px; color: var(--text-muted); }

/* ──────────────────────────────────────────────
   MOCKUP 2 — PROFILE / SHARES
   ────────────────────────────────────────────── */
.mk-profile-head { display: flex; gap: 14px; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border); margin-bottom: 14px; }
.mk-profile-ava {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), #8b5cf6);
    color: white; display: grid; place-items: center;
    font-family: var(--display); font-size: 22px; font-weight: 700;
}
.mk-profile-name { font-family: var(--display); font-size: 18px; font-weight: 600; color: var(--text); }
.mk-profile-sub  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

.mk-myteams { display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px; }
.mk-myteam {
    display: grid; grid-template-columns: 44px 1fr auto; gap: 12px; align-items: center;
    padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px;
    background: var(--surface);
}
.mk-myteam-logo { width: 44px; height: 44px; border-radius: 10px; background: var(--c); color: white; display: grid; place-items: center; font-size: 18px; }
.mk-myteam-name { font-size: 13.5px; font-weight: 600; color: var(--text); }
.mk-myteam-role { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
.mk-myteam-cta  { font-size: 11px; font-weight: 600; color: var(--accent); }

.mk-shares { display: flex; flex-direction: column; gap: 10px; margin-bottom: 4px; }
.mk-share-row {
    display: grid; grid-template-columns: 1.2fr 2fr 1fr; gap: 12px; align-items: center;
    padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px;
}
.mk-share-name { font-size: 13px; font-weight: 600; color: var(--text); }
.mk-share-bar {
    position: relative; height: 22px; border-radius: 6px;
    background: var(--surface-2); overflow: hidden;
}
.mk-share-fill { height: 100%; border-radius: 6px; transition: width .4s; }
.mk-share-pct {
    position: absolute; inset: 0; display: grid; place-items: center;
    font-size: 11px; font-weight: 700; color: var(--text);
    letter-spacing: .04em; text-transform: uppercase;
}
.mk-share-meta { font-size: 11.5px; color: var(--text-muted); text-align: right; }

@media (max-width: 700px) {
    .mk-share-row { grid-template-columns: 1fr; gap: 8px; }
    .mk-share-meta { text-align: left; }
}

.mk-log { margin-top: 10px; }
.mk-log-row {
    display: grid; grid-template-columns: 36px 1fr; gap: 10px; align-items: center;
    padding: 10px 0; border-bottom: 1px dashed var(--border);
}
.mk-log-row:last-child { border-bottom: 0; }
.mk-log-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: color-mix(in srgb, var(--c) 14%, transparent);
    color: var(--c); display: grid; place-items: center; font-size: 13px;
}
.mk-log-title { font-size: 13px; color: var(--text); font-weight: 600; }
.mk-log-sub   { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }

/* ──────────────────────────────────────────────
   MOCKUP 3 — FINANCE DASHBOARD
   ────────────────────────────────────────────── */
.mk-fin-summary {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 18px;
}
@media (max-width: 720px) { .mk-fin-summary { grid-template-columns: 1fr 1fr; } }
.mk-fin-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
    padding: 14px; display: flex; flex-direction: column; gap: 6px;
    border-top: 3px solid var(--c);
}
.mk-fin-icon {
    width: 32px; height: 32px; border-radius: 8px;
    background: color-mix(in srgb, var(--c) 14%, transparent);
    color: var(--c); display: grid; place-items: center; font-size: 13px;
    margin-bottom: 6px;
}
.mk-fin-lbl { font-size: 10.5px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
.mk-fin-val { font-size: 13px; font-weight: 600; color: var(--text); line-height: 1.3; }

.mk-fin-chart {
    background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
    padding: 14px 16px 8px; margin-bottom: 14px;
}
.mk-svg { width: 100%; height: auto; display: block; }

.mk-fin-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 720px) { .mk-fin-bottom { grid-template-columns: 1fr; } }
.mk-fin-titles { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 14px; }
.mk-fin-list, .mk-fin-payout { display: flex; flex-direction: column; gap: 8px; }
.mk-fin-list-row {
    display: grid; grid-template-columns: 24px 1.4fr 2fr; gap: 8px; align-items: center;
    font-size: 12px;
}
.mk-fin-list-rank {
    background: var(--surface-2); border-radius: 6px;
    text-align: center; font-family: var(--mono, monospace); font-weight: 700;
    padding: 3px 0; color: var(--accent);
}
.mk-fin-list-name { color: var(--text); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mk-fin-list-bar { height: 8px; background: var(--surface-2); border-radius: 99px; overflow: hidden; }
.mk-fin-list-bar > div { height: 100%; border-radius: 99px; }

.mk-payout-row {
    display: flex; justify-content: space-between; align-items: center; gap: 8px;
    padding: 6px 0; border-bottom: 1px dashed var(--border);
    font-size: 12px;
}
.mk-payout-row:last-child { border-bottom: 0; }
.mk-payout-name { color: var(--text); font-weight: 600; }
.mk-payout-name span { color: var(--text-muted); font-weight: 400; }
.mk-payout-when { color: var(--text-muted); font-size: 11px; }

/* ──────────────────────────────────────────────
   MOCKUP 4 — GLOSSARY
   ────────────────────────────────────────────── */
.mk-glossary-head { display: flex; gap: 12px; align-items: center; margin-bottom: 14px; flex-wrap: wrap; }
.mk-search-fake {
    flex: 1; display: flex; gap: 8px; align-items: center;
    background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px;
    padding: 7px 12px; font-size: 12.5px; color: var(--text-muted);
    min-width: 200px;
}
.mk-glossary-tabs { display: flex; gap: 0; flex-wrap: wrap; }
.mk-glossary-table {
    width: 100%; border-collapse: collapse; font-size: 12.5px;
    background: var(--surface); border: 1px solid var(--border); border-radius: 8px; overflow: hidden;
}
.mk-glossary-table th, .mk-glossary-table td { padding: 9px 12px; text-align: left; border-bottom: 1px solid var(--border); }
.mk-glossary-table th { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); background: var(--surface-2); }
.mk-glossary-table tbody tr:last-child td { border-bottom: 0; }
.mk-glossary-orig { font-family: var(--mono, monospace); color: var(--text-2); }
.mk-glossary-pill {
    display: inline-block; background: var(--surface-2);
    padding: 2px 9px; border-radius: 99px; font-size: 10.5px; color: var(--text-2);
}
.mk-glossary-note { color: var(--text-muted); font-size: 11.5px; }

/* ──────────────────────────────────────────────
   MOCKUP 5 — CHAT
   ────────────────────────────────────────────── */
.mk-chat { display: grid; grid-template-columns: 180px 1fr; min-height: 360px; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
@media (max-width: 640px) { .mk-chat { grid-template-columns: 1fr; } .mk-chat-side { display: none; } }

.mk-chat-side { background: var(--surface-2); padding: 14px 12px; }
.mk-chat-side-title { font-family: var(--mono, monospace); font-size: 10px; font-weight: 700; color: var(--text-muted); letter-spacing: .14em; text-transform: uppercase; margin-bottom: 10px; }
.mk-chat-channel {
    padding: 7px 10px; border-radius: 6px; font-size: 13px; color: var(--text-2); margin-bottom: 2px; cursor: default;
}
.mk-chat-channel.on { background: var(--surface); color: var(--accent); font-weight: 600; }
.mk-chat-channel em { color: var(--err, #ef4444); font-style: normal; }

.mk-chat-main { background: var(--surface); padding: 14px 18px; display: flex; flex-direction: column; gap: 12px; }
.mk-chat-msg { display: grid; grid-template-columns: 32px 1fr; gap: 10px; }
.mk-chat-ava {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--c); color: white; display: grid; place-items: center;
    font-weight: 700; font-size: 12px; flex: none;
}
.mk-chat-meta { display: flex; gap: 8px; align-items: baseline; font-size: 11.5px; color: var(--text-muted); margin-bottom: 2px; }
.mk-chat-meta b { color: var(--text); font-size: 13px; }
.mk-chat-text { font-size: 13px; color: var(--text-2); line-height: 1.5; }
.mk-chat-mention { color: var(--accent); font-weight: 600; }
.mk-chat-react { margin-top: 6px; display: flex; gap: 6px; }
.mk-chat-react span { font-size: 11px; background: var(--surface-2); padding: 2px 8px; border-radius: 99px; }
.mk-chat-attach {
    margin-top: 6px; display: inline-flex; gap: 6px; align-items: center;
    background: var(--surface-2); border: 1px solid var(--border); border-radius: 6px;
    padding: 4px 10px; font-size: 11.5px; color: var(--text-2);
}
.mk-chat-input {
    margin-top: auto; display: flex; gap: 10px; align-items: center;
    padding: 8px 12px; background: var(--surface-2); border-radius: 8px;
    font-size: 12.5px; color: var(--text-muted);
}
.mk-chat-input i { color: var(--text-3); }
.mk-chat-input span { flex: 1; }

/* ──────────────────────────────────────────────
   ROLES
   ────────────────────────────────────────────── */
.tsoon-roles-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
}
@media (max-width: 900px) { .tsoon-roles-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .tsoon-roles-grid { grid-template-columns: 1fr; } }
.tsoon-role-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
    padding: 18px; border-top: 3px solid var(--c);
    transition: transform .2s, box-shadow .2s;
}
.tsoon-role-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(15,20,25,0.06); }
.tsoon-role-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: color-mix(in srgb, var(--c) 14%, transparent);
    color: var(--c); display: grid; place-items: center; font-size: 15px;
    margin-bottom: 12px;
}
.tsoon-role-name { font-family: var(--display); font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.tsoon-role-desc { font-size: 12.5px; color: var(--text-muted); line-height: 1.55; }

/* ──────────────────────────────────────────────
   FLOW
   ────────────────────────────────────────────── */
.tsoon-flow { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
@media (max-width: 900px) { .tsoon-flow { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .tsoon-flow { grid-template-columns: 1fr; } }
.tsoon-flow-step {
    background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
    padding: 22px; transition: border-color .2s, transform .2s;
}
.tsoon-flow-step:hover { border-color: var(--accent); transform: translateY(-2px); }
.tsoon-flow-num {
    font-family: var(--display); font-size: 36px; font-weight: 600;
    color: color-mix(in srgb, var(--accent) 40%, transparent);
    line-height: 1; margin-bottom: 12px;
}
.tsoon-flow-title { font-family: var(--display); font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
.tsoon-flow-desc  { font-size: 13px; color: var(--text-muted); line-height: 1.55; }

/* ──────────────────────────────────────────────
   FAQ
   ────────────────────────────────────────────── */
.tsoon-faq { max-width: 760px; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; }
.tsoon-faq-item { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: border-color .2s; }
.tsoon-faq-item.is-open { border-color: color-mix(in srgb, var(--accent) 40%, var(--border)); }
.tsoon-faq-q {
    width: 100%; appearance: none; border: 0; cursor: pointer;
    background: transparent; color: var(--text);
    padding: 16px 20px; font-family: inherit;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    font-size: 14.5px; font-weight: 600; text-align: left;
}
.tsoon-faq-q i { color: var(--text-muted); font-size: 12px; transition: transform .25s; flex-shrink: 0; }
.tsoon-faq-item.is-open .tsoon-faq-q i { transform: rotate(180deg); color: var(--accent); }
.tsoon-faq-a { padding: 0 20px 18px; border-top: 1px solid color-mix(in srgb, var(--accent) 10%, var(--border)); }
.tsoon-faq-a p { margin: 14px 0 0; font-size: 14px; color: var(--text-2); line-height: 1.65; }

/* ──────────────────────────────────────────────
   CTA
   ────────────────────────────────────────────── */
.tsoon-cta { margin: 60px auto 0; max-width: 880px; padding: 0 24px; }
.tsoon-cta-inner {
    background: linear-gradient(135deg, var(--accent), color-mix(in srgb, var(--accent) 70%, #8b5cf6));
    color: white; border-radius: 24px;
    padding: 56px 36px; text-align: center;
    position: relative; overflow: hidden;
    box-shadow: 0 20px 50px color-mix(in srgb, var(--accent) 30%, transparent);
}
.tsoon-cta-inner::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15), transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1),  transparent 40%);
    pointer-events: none;
}
.tsoon-cta-title { font-family: var(--display); font-size: clamp(24px, 3.5vw, 36px); font-weight: 600; line-height: 1.2; margin: 0 0 14px; color: white; position: relative; z-index: 1; }
.tsoon-cta-sub   { font-size: 15px; line-height: 1.6; opacity: 0.92; margin: 0 auto 28px; max-width: 540px; position: relative; z-index: 1; }
.tsoon-cta-form  { display: flex; gap: 10px; max-width: 520px; margin: 0 auto; flex-wrap: wrap; position: relative; z-index: 1; }
.tsoon-cta-form input {
    flex: 1; padding: 14px 18px; border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.95);
    color: var(--text); font-size: 14px;
}
.tsoon-cta-fine { font-size: 12px; opacity: 0.75; margin: 18px 0 0; position: relative; z-index: 1; }
@media (max-width: 480px) {
    .tsoon-cta-inner { padding: 40px 22px; }
    .tsoon-cta-form { flex-direction: column; }
}
</style>
@endsection
