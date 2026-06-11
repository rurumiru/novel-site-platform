@php
    $siteName = \App\Models\Setting::retrieve('site_name', 'eriiba');
@endphp
<footer class="eri-footer">
    <div class="eri-footer-inner" style="grid-template-columns: 2fr 1fr 1fr;">
        <div>
            <div class="eri-logo" style="margin-bottom:12px;">
                <span>{{ $siteName }}</span>
            </div>
            <p style="font-size:13px;color:var(--text-3);max-width:420px;margin:0;line-height:1.55;">
                Сайт создан для тестов функционала и для команды <strong style="color:var(--text-2);">«Эльфийские Сказания»</strong>.
            </p>
        </div>
        <div>
            <h4>Разделы</h4>
            <ul>
                <li><a href="{{ route('catalog') }}" wire:navigate>Каталог</a></li>
                <li><a href="{{ route('updates') }}" wire:navigate>Обновления</a></li>
                <li><a href="{{ route('rankings') }}" wire:navigate>Рейтинги</a></li>
                <li><a href="{{ route('blog.index') }}" wire:navigate>Блог</a></li>
                <li><a href="{{ route('users.index') }}" wire:navigate>Люди</a></li>
            </ul>
        </div>
        <div>
            <h4>Информация</h4>
            <ul>
                <li><a href="{{ url('/info/about') }}" wire:navigate>О проекте</a></li>
                <li><a href="{{ url('/info/rules') }}" wire:navigate>Правила</a></li>
                <li><a href="{{ url('/info/privacy') }}" wire:navigate>Конфиденциальность</a></li>
            </ul>
        </div>
    </div>
    <div class="eri-footer-bottom">
        <span>© {{ date('Y') }} {{ $siteName }}</span>
        <span>Команда «Эльфийские Сказания»</span>
    </div>
</footer>
