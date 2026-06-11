@extends('layouts.app')
@section('title', 'Подписка')

@section('content')
<div class="max-w-5xl mx-auto px-4 pt-24 pb-16">

    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-star text-[10px]"></i> Поддержите авторов
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 dark:text-white mb-4 tracking-tight">
            Выберите тариф
        </h1>
        <p class="text-base text-slate-500 dark:text-slate-400 max-w-xl mx-auto">
            Оформите подписку, чтобы получить доступ к эксклюзивным главам и поддержать развитие платформы.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:gap-6 items-stretch">

        <div class="flex flex-col bg-white dark:bg-slate-900 rounded-2xl p-7 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition duration-300">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-5">
                <i class="fa-solid fa-book text-slate-500 dark:text-slate-400 text-lg"></i>
            </div>
            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mb-1">Читатель</h3>
            <div class="flex items-baseline gap-1 mb-6">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">0 ₽</span>
                <span class="text-sm text-slate-400">/ мес</span>
            </div>
            <ul class="space-y-3 mb-8 flex-grow text-sm text-slate-600 dark:text-slate-400">
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 text-xs w-4 flex-shrink-0"></i>
                    Доступ к бесплатным главам
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 text-xs w-4 flex-shrink-0"></i>
                    Комментарии и оценки
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-emerald-500 text-xs w-4 flex-shrink-0"></i>
                    Библиотека и закладки
                </li>
            </ul>
            <a href="{{ route('register') }}"
               class="block w-full py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-center text-sm transition">
                Зарегистрироваться
            </a>
        </div>

        <div class="flex flex-col relative bg-gradient-to-b from-emerald-500 to-teal-600 rounded-2xl p-7 shadow-2xl shadow-emerald-500/25 ring-1 ring-emerald-400/30 md:-translate-y-3">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white text-emerald-600 text-xs font-black shadow-lg uppercase tracking-wide">
                    <i class="fa-solid fa-bolt text-[10px]"></i> Популярное
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center mb-5">
                <i class="fa-solid fa-crown text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-extrabold text-white mb-1">Премиум</h3>
            <div class="flex items-baseline gap-1 mb-6">
                <span class="text-3xl font-extrabold text-white">199 ₽</span>
                <span class="text-sm text-white/70">/ мес</span>
            </div>
            <ul class="space-y-3 mb-8 flex-grow text-sm text-white/90">
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-white text-xs w-4 flex-shrink-0"></i>
                    Всё из тарифа Читатель
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-white text-xs w-4 flex-shrink-0"></i>
                    Доступ к платным главам
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-white text-xs w-4 flex-shrink-0"></i>
                    Отключение рекламы
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-white text-xs w-4 flex-shrink-0"></i>
                    Бейджик в профиле
                </li>
            </ul>
            <button class="block w-full py-3 bg-white hover:bg-slate-50 text-emerald-600 font-bold rounded-xl text-center text-sm transition shadow-lg shadow-black/10">
                Оформить
            </button>
        </div>

        <div class="flex flex-col bg-white dark:bg-slate-900 rounded-2xl p-7 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition duration-300">
            <div class="w-11 h-11 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mb-5">
                <i class="fa-solid fa-gem text-violet-500 text-lg"></i>
            </div>
            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mb-1">Меценат</h3>
            <div class="flex items-baseline gap-1 mb-6">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">499 ₽</span>
                <span class="text-sm text-slate-400">/ мес</span>
            </div>
            <ul class="space-y-3 mb-8 flex-grow text-sm text-slate-600 dark:text-slate-400">
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-violet-500 text-xs w-4 flex-shrink-0"></i>
                    Все преимущества Премиум
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-violet-500 text-xs w-4 flex-shrink-0"></i>
                    Ранний доступ к новинкам
                </li>
                <li class="flex items-center gap-2.5">
                    <i class="fa-solid fa-check text-violet-500 text-xs w-4 flex-shrink-0"></i>
                    Уникальная рамка аватара
                </li>
            </ul>
            <button class="block w-full py-3 bg-violet-600 hover:bg-violet-500 text-white font-bold rounded-xl text-center text-sm transition shadow-md shadow-violet-500/20">
                Поддержать
            </button>
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-8">Вопросы по оплате? <a href="{{ route('home') }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">Свяжитесь с нами</a></p>
</div>
@endsection
