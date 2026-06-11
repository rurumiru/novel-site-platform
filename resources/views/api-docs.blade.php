@extends('layouts.app')
@section('title', 'API Документация')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">REST API v1</h1>
    <p class="text-slate-600 dark:text-slate-400 mb-8">Публичный API для доступа к каталогу новелл, главам и авторам. Базовый URL: <code class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{{ url('/api/v1') }}</code></p>

    <div class="space-y-8">
        <section class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">Новеллы</h2>
            <dl class="space-y-4 font-mono text-sm">
                <div><dt class="text-slate-500">GET /api/v1/novels</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Список новелл. Параметры: page, per_page (max 50), sort (latest|popular|rating), search, status, tags (через запятую)</dd></div>
                <div><dt class="text-slate-500">GET /api/v1/novels/{id}</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Одна новелла с томами и главами</dd></div>
                <div><dt class="text-slate-500">GET /api/v1/novels/{id}/chapters</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Список глав новеллы</dd></div>
                <div><dt class="text-slate-500">GET /api/v1/updates</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Последние обновления (свежие главы)</dd></div>
            </dl>
        </section>

        <section class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">Главы</h2>
            <dl class="space-y-4 font-mono text-sm">
                <div><dt class="text-slate-500">GET /api/v1/chapters/{id}</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Одна глава (с контентом)</dd></div>
            </dl>
        </section>

        <section class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">Авторы</h2>
            <dl class="space-y-4 font-mono text-sm">
                <div><dt class="text-slate-500">GET /api/v1/authors</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Список авторов (у кого есть опубликованные новеллы)</dd></div>
                <div><dt class="text-slate-500">GET /api/v1/authors/{id}</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Профиль автора</dd></div>
                <div><dt class="text-slate-500">GET /api/v1/authors/{id}/novels</dt><dd class="text-slate-700 dark:text-slate-300 mt-1">Новеллы автора</dd></div>
            </dl>
        </section>

        <p class="text-slate-500 text-sm">Ответы в формате JSON. Пагинация: meta.current_page, meta.last_page, meta.per_page, links.prev, links.next</p>
    </div>
</div>
@endsection
