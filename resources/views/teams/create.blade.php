@extends('layouts.app')
@section('title', 'Создать команду')
@section('content')
<div class="eri-page">
    <section class="eri-section" style="max-width:720px;">
        <div style="display:flex; align-items:center; gap:14px; margin-bottom:28px;">
            <a href="{{ route('teams.index') }}" wire:navigate class="eri-btn sm">
                <i class="fa-solid fa-arrow-left"></i> Назад
            </a>
            <h1 style="font-family:var(--display); font-size:28px; font-weight:600; letter-spacing:-0.02em; margin:0; color:var(--text);">
                Создать команду
            </h1>
        </div>

        @if($errors->any())
            <div class="eri-alert err" style="margin-bottom:20px;">
                <strong><i class="fa-solid fa-circle-exclamation"></i> Исправьте ошибки:</strong>
                <ul style="margin:8px 0 0; padding-left:18px;">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('teams.store') }}">
            @csrf

            <div class="eri-card" style="margin-bottom:16px;">
                <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 18px; color:var(--text);">
                    <i class="fa-solid fa-pen-nib" style="color:var(--accent); margin-right:8px;"></i>Основное
                </h2>

                <div style="display:grid; gap:14px;">
                    <div>
                        <label class="eri-label">Название команды <span style="color:var(--err);">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="eri-input" placeholder="Команда переводчиков XYZ"
                               maxlength="120" required autofocus>
                    </div>

                    <div>
                        <label class="eri-label">Девиз / миссия <span style="color:var(--text-muted); font-weight:400;">(до 280 зн.)</span></label>
                        <input type="text" name="mission" value="{{ old('mission') }}"
                               class="eri-input" placeholder="Качественные переводы без промедления"
                               maxlength="280">
                    </div>

                    <div>
                        <label class="eri-label">Описание команды</label>
                        <textarea name="description" rows="4" class="eri-textarea"
                                  placeholder="Расскажите о команде: история, специализация, подход к работе…"
                                  maxlength="4000">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="eri-card" style="margin-bottom:16px;">
                <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 18px; color:var(--text);">
                    <i class="fa-solid fa-sliders" style="color:var(--accent); margin-right:8px;"></i>Настройки
                </h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label class="eri-label">Статус команды</label>
                        <select name="status" class="eri-select">
                            <option value="recruiting" {{ old('status', 'recruiting') === 'recruiting' ? 'selected' : '' }}>🟢 Набор открыт</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>🔵 Активна</option>
                            <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>⚫ Закрыта</option>
                            <option value="on_hiatus" {{ old('status') === 'on_hiatus' ? 'selected' : '' }}>⏸ Пауза</option>
                        </select>
                    </div>

                    <div>
                        <label class="eri-label">Вступление</label>
                        <select name="application_mode" class="eri-select">
                            <option value="invite_only" {{ old('application_mode', 'invite_only') === 'invite_only' ? 'selected' : '' }}>🔒 Только по приглашению</option>
                            <option value="open" {{ old('application_mode') === 'open' ? 'selected' : '' }}>📬 Открытые заявки</option>
                            <option value="closed" {{ old('application_mode') === 'closed' ? 'selected' : '' }}>🚫 Закрыто</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="eri-card" style="margin-bottom:24px;">
                <h2 style="font-family:var(--display); font-size:18px; font-weight:500; margin:0 0 18px; color:var(--text);">
                    <i class="fa-solid fa-share-nodes" style="color:var(--accent); margin-right:8px;"></i>Контакты
                    <span style="font-size:13px; font-weight:400; color:var(--text-muted); margin-left:6px;">— необязательно</span>
                </h2>

                <div style="display:grid; gap:12px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-brands fa-discord" style="color:#5865f2; width:20px; font-size:16px; flex-shrink:0;"></i>
                        <input type="url" name="discord" value="{{ old('discord') }}" class="eri-input" placeholder="https://discord.gg/...">
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-brands fa-telegram" style="color:#0088cc; width:20px; font-size:16px; flex-shrink:0;"></i>
                        <input type="url" name="telegram" value="{{ old('telegram') }}" class="eri-input" placeholder="https://t.me/...">
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-brands fa-vk" style="color:#0077ff; width:20px; font-size:16px; flex-shrink:0;"></i>
                        <input type="url" name="vk" value="{{ old('vk') }}" class="eri-input" placeholder="https://vk.com/...">
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-globe" style="color:var(--text-muted); width:20px; font-size:16px; flex-shrink:0;"></i>
                        <input type="url" name="website" value="{{ old('website') }}" class="eri-input" placeholder="https://...">
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px;">
                <a href="{{ route('teams.index') }}" wire:navigate class="eri-btn block">Отмена</a>
                <button type="submit" class="eri-btn primary block">
                    <i class="fa-solid fa-check"></i> Создать команду
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
