<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Демо-режим
    |--------------------------------------------------------------------------
    |
    | Когда включён, публичный стенд периодически удаляет ВЕСЬ контент и
    | активность, не помеченные как демонстрационные (is_demo = true), и
    | полностью сбрасывает пользовательские взаимодействия. Это позволяет дать
    | посетителям полноценно потестировать сайт и админку, при этом стенд сам
    | возвращается к исходному (эталонному) состоянию.
    |
    | ВНИМАНИЕ: включайте только на изолированном демо-окружении. На боевом
    | сервере с реальными данными это приведёт к их удалению.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    // Как часто планировщик чистит не-демо контент (минуты).
    'cleanup_interval_minutes' => (int) env('DEMO_CLEANUP_MINUTES', 15),

    // Учётные данные тестового администратора (создаются DemoSeeder).
    'admin_email'    => env('DEMO_ADMIN_EMAIL', 'admin@demo.iiiba.ru'),
    'admin_password' => env('DEMO_ADMIN_PASSWORD', 'demo12345'),

    // Роли, аккаунты с которыми НЕ удаляются при очистке (staff). Все остальные
    // незащищённые пользователи (включая зарегистрировавшихся посетителей без роли
    // или с ролью «user») удаляются.
    'protected_roles' => ['owner', 'super_admin', 'deputy_admin', 'moderator', 'editor'],

    /*
    |--------------------------------------------------------------------------
    | Таблицы с эталонным (демо) контентом
    |--------------------------------------------------------------------------
    | Имеют колонку is_demo. Эталон помечается true (см. команду demo:snapshot),
    | всё, что добавили посетители (is_demo = false), удаляется при очистке.
    | Порядок — «потомки раньше родителей» для безопасности по внешним ключам.
    | Таблица users обрабатывается отдельно (staff-аккаунты не трогаются).
    */
    'flagged_tables' => [
        'forum_posts',
        'forum_threads',
        'posts',
        'reviews',
        'chapters',
        'volumes',
        'novels',
    ],

    /*
    |--------------------------------------------------------------------------
    | Таблицы чистой пользовательской активности
    |--------------------------------------------------------------------------
    | Эталонного контента в них нет — очищаются полностью при каждом сбросе.
    | Порядок — «потомки раньше родителей».
    */
    'wipe_tables' => [
        // комментарии и реакции
        'comment_likes',
        'comment_recommendations',
        'comment_reports',
        'comments',
        'review_recommends',
        // взаимодействие с главами
        'chapter_likes',
        'chapter_bookmarks',
        'chapter_versions',
        'chapter_errors',
        'reading_progress',
        // оценки и избранное
        'ratings',
        'favorites',
        // форум (структура категорий/секций сохраняется)
        'forum_reactions',
        'forum_reports',
        'forum_participants',
        'forum_subscriptions',
        'forum_thread_reads',
        'forum_thread_tag',
        'forum_action_logs',
        // личные сообщения и уведомления
        'messages',
        'notifications',
        // монетизация / доступы
        'promo_code_usages',
        'user_unlocked_chapters',
        'novel_accesses',
        'novel_views',
        'novel_promotions',
        'subscriptions',
        'transactions',
        'donations',
        'withdrawals',
        // заявки и обращения
        'email_change_requests',
        'recruitment_applications',
        'support_replies',
        'support_tickets',
        // команды и коллаборация
        'beta_reader_notes',
        'beta_readers',
        'novel_editors',
        'novel_team_member_shares',
        'team_payouts',
        'team_kick_log',
        'team_invites',
        'team_applications',
        'novel_team',
        'translation_team_members',
        'translation_teams',
        // персональные настройки
        'user_homepage_layouts',
    ],

];
