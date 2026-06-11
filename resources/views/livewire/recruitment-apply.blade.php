<div>
    <div class="eri-section" style="padding-top:24px;padding-bottom:0;">
        <a href="{{ route('recruitment.index') }}" wire:navigate
           style="font-family:var(--mono);font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-3);text-decoration:none;font-weight:600;">
            ← Назад к вакансиям
        </a>
    </div>

    <div class="eri-section" style="padding-top:18px;">
        @if($success)
            <div class="eri-card eri-card-pad-lg" style="max-width:560px;margin:0 auto;text-align:center;">
                <div style="font-size:48px;color:var(--ok);margin-bottom:12px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h1 style="font-family:var(--display);font-size:28px;font-weight:500;letter-spacing:-0.02em;margin:0 0 10px;color:var(--text);">
                    Отклик отправлен
                </h1>
                <p style="font-family:var(--serif);font-size:16px;color:var(--text-2);margin:0 0 22px;">
                    Спасибо за интерес. Мы рассмотрим вашу заявку и свяжемся с вами по указанным контактам.
                </p>
                <a href="{{ route('recruitment.index') }}" wire:navigate class="eri-btn primary">К другим вакансиям</a>
            </div>
        @else
            <div style="max-width:640px;margin:0 auto;">
                <div style="font-family:var(--mono);font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:var(--accent);font-weight:700;margin-bottom:14px;">
                    Отклик · вакансия
                </div>
                <h1 style="font-family:var(--display);font-size:clamp(28px,4vw,40px);font-weight:500;letter-spacing:-0.025em;line-height:1.05;margin:0 0 8px;color:var(--text);text-wrap:balance;">
                    {{ $post->title }}
                </h1>
                <p style="font-family:var(--serif);font-size:15px;color:var(--text-3);margin:0 0 24px;">
                    Заполните анкету — это займёт пару минут. Поля со звёздочкой обязательны.
                </p>

                <div class="eri-card eri-card-pad-lg">
                    @if(session('error'))
                        <div class="eri-alert err" style="margin-bottom:18px;">{{ session('error') }}</div>
                    @endif

                    <form wire:submit="submit" style="display:flex;flex-direction:column;gap:18px;">
                        @csrf

                        <div>
                            <label class="eri-label" for="rec-name">Имя *</label>
                            <input id="rec-name" type="text" wire:model="name" class="eri-input" placeholder="Ваше имя">
                            @error('name') <div class="eri-alert err" style="margin-top:6px;padding:6px 10px;font-size:12px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="eri-label" for="rec-email">Email *</label>
                            <input id="rec-email" type="email" wire:model="email" class="eri-input" placeholder="email@example.com">
                            @error('email') <div class="eri-alert err" style="margin-top:6px;padding:6px 10px;font-size:12px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="eri-label" for="rec-contact">Контакт (Telegram, Discord)</label>
                            <input id="rec-contact" type="text" wire:model="contact" class="eri-input" placeholder="@username или ссылка">
                        </div>

                        <div>
                            <label class="eri-label" for="rec-experience">Опыт</label>
                            <textarea id="rec-experience" wire:model="experience" rows="4" class="eri-textarea"
                                      placeholder="Опишите релевантный опыт: модерация, администрирование, работа в команде."></textarea>
                        </div>

                        <div>
                            <label class="eri-label" for="rec-skills">Навыки</label>
                            <textarea id="rec-skills" wire:model="skills" rows="3" class="eri-textarea"
                                      placeholder="Чем владеете? Какие инструменты знаете?"></textarea>
                        </div>

                        <div>
                            <label class="eri-label" for="rec-motivation">Почему хотите присоединиться?</label>
                            <textarea id="rec-motivation" wire:model="motivation" rows="4" class="eri-textarea"
                                      placeholder="Кратко расскажите о вашей мотивации."></textarea>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="eri-btn primary block">
                            <span wire:loading.remove>Отправить отклик</span>
                            <span wire:loading>Отправка…</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
