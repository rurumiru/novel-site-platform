<div class="eri-card eri-card-pad-lg" style="max-width:760px;">

    @if(session('success'))
        <div class="eri-alert ok" style="margin-bottom:20px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="eri-alert err" style="margin-bottom:20px;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" class="eri-col" style="gap:18px;">
        @csrf

        <div>
            <label class="eri-label" for="set-name">Имя</label>
            <input id="set-name" type="text" name="name" value="{{ old('name', $user->name) }}" class="eri-input">
            @error('name') <div class="eri-error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="eri-label">Логин</label>
            @if($user->username)
                <div class="eri-input" style="display:flex;align-items:center;gap:10px;cursor:default;">
                    <span style="color:var(--text-muted);font-weight:600;">@</span>
                    <span style="font-family:var(--mono);color:var(--text);">{{ $user->username }}</span>
                    <span style="margin-left:auto;font-size:11px;color:var(--text-muted);">
                        <i class="fa-solid fa-lock"></i> Изменить нельзя
                    </span>
                </div>
                <div class="eri-help">Логин задаётся один раз. Смена — только через администратора.</div>
            @else
                <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;">@</span>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="my_login"
                           class="eri-input" style="padding-left:30px;"
                           pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30">
                </div>
                <div class="eri-help" style="color:var(--warn);">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Внимание: после сохранения логин изменить нельзя.
                </div>
                @error('username') <div class="eri-error">{{ $message }}</div> @enderror
            @endif
        </div>

        <div class="eri-card" style="padding:18px;{{ $user->hasAgeInfo() ? '' : 'border-color:var(--err);background:color-mix(in srgb,var(--err) 5%,var(--surface));' }}">
            <div class="eri-row" style="margin-bottom:12px;gap:8px;">
                <span style="font-family:var(--mono);font-size:11px;font-weight:800;color:var(--err);letter-spacing:.06em;">18+</span>
                <span class="eri-label" style="margin:0;">Возрастная верификация</span>
            </div>

            @if($user->hasAgeInfo())
                <div class="eri-row" style="gap:12px;flex-wrap:wrap;">
                    <div class="eri-input" style="flex:1;min-width:220px;display:flex;align-items:center;gap:10px;cursor:default;">
                        <i class="fa-solid fa-cake-candles" style="color:var(--text-muted);"></i>
                        <span>{{ $user->birth_date->format('d.m.Y') }}</span>
                        <span style="margin-left:auto;font-size:11px;color:var(--text-muted);"><i class="fa-solid fa-lock"></i></span>
                    </div>
                    <div class="eri-input" style="flex:1;min-width:220px;display:flex;align-items:center;gap:10px;cursor:default;">
                        <i class="fa-solid fa-venus-mars" style="color:var(--text-muted);"></i>
                        <span>
                            @switch($user->gender)
                                @case('male') Мужчина @break
                                @case('female') Женщина @break
                                @default Скрыт
                            @endswitch
                        </span>
                        <span style="margin-left:auto;font-size:11px;color:var(--text-muted);"><i class="fa-solid fa-lock"></i></span>
                    </div>
                </div>
            @else
                <p class="eri-help" style="color:var(--err);margin-bottom:12px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Укажите дату рождения и пол — без них недоступен контент 18+. Данные задаются один раз и не меняются.
                </p>
                <div class="eri-row" style="gap:12px;flex-wrap:wrap;align-items:flex-start;">
                    <div style="flex:1;min-width:220px;">
                        <label class="eri-label" for="set-birth">Дата рождения</label>
                        <input id="set-birth" type="date" name="birth_date" required
                               max="{{ now()->subYears(5)->format('Y-m-d') }}" min="1920-01-01"
                               value="{{ old('birth_date') }}" class="eri-input">
                        @error('birth_date') <div class="eri-error">{{ $message }}</div> @enderror
                    </div>
                    <div style="flex:1;min-width:220px;">
                        <label class="eri-label">Пол</label>
                        <div class="eri-row" style="gap:6px;" x-data="{ g: '{{ old('gender') }}' }">
                            @foreach(['male' => 'Мужчина', 'female' => 'Женщина', 'hidden' => 'Скрыть'] as $value => $label)
                                <label style="flex:1;cursor:pointer;display:block;">
                                    <input type="radio" name="gender" value="{{ $value }}" @checked(old('gender') === $value) required
                                           x-model="g" style="position:absolute;opacity:0;width:0;height:0;">
                                    <span class="eri-btn" :class="g === '{{ $value }}' ? 'primary' : ''"
                                          style="width:100%;display:flex;justify-content:center;">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('gender') <div class="eri-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            @endif
        </div>

        <div>
            <label class="eri-label" for="set-bio">О себе</label>
            <textarea id="set-bio" name="bio" rows="3" maxlength="1000"
                      placeholder="Расскажите немного о себе..." class="eri-textarea">{{ old('bio', $user->bio_pending ?? $user->bio) }}</textarea>
            @if($user->bio_pending && $user->bio_pending !== $user->bio)
                <div class="eri-help" style="color:var(--warn);">
                    <i class="fa-solid fa-clock"></i> Новое описание на модерации
                </div>
            @endif
            @error('bio') <div class="eri-error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="eri-label" for="set-social">Соцсеть для связи</label>
            <input id="set-social" type="text" name="social_link" value="{{ old('social_link', $user->social_link) }}"
                   placeholder="https://t.me/..." class="eri-input">
            @error('social_link') <div class="eri-error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="eri-label" for="set-donation">Ссылка на поддержку (донат)</label>
            <input id="set-donation" type="url" name="donation_link" value="{{ old('donation_link', $user->donation_link) }}"
                   placeholder="https://..." class="eri-input">
            @if($user->donation_link && !$user->is_donation_link_approved)
                <div class="eri-help" style="color:var(--warn);">
                    <i class="fa-solid fa-clock"></i> Ссылка ожидает одобрения модератором
                </div>
            @endif
            @error('donation_link') <div class="eri-error">{{ $message }}</div> @enderror
        </div>

        @if($user->hasRole('author') || $user->hasRole('super_admin'))
            <div>
                <label class="eri-label" for="set-bundle">Скидка на пакет автора (%)</label>
                <input id="set-bundle" type="number" name="bundle_discount"
                       value="{{ old('bundle_discount', $user->bundle_discount ?? 15) }}"
                       min="0" max="90" class="eri-input">
                <div class="eri-help">Процент скидки при покупке всех ваших работ пакетом.</div>
                @error('bundle_discount') <div class="eri-error">{{ $message }}</div> @enderror
            </div>
        @endif

        <div>
            <x-eriiba.btn variant="primary" type="submit">
                <i class="fa-solid fa-floppy-disk"></i> Сохранить
            </x-eriiba.btn>
        </div>
    </form>

    @php
        $emailChange = \App\Models\EmailChangeRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending_code', 'awaiting_admin'])
            ->latest()
            ->first();
    @endphp

    <div style="margin-top:36px;padding-top:28px;border-top:1px solid var(--border);">
        <h3 style="font-family:var(--display);font-size:20px;font-weight:500;letter-spacing:-0.01em;margin:0 0 6px;color:var(--text);">
            Смена email
        </h3>
        <p class="eri-help" style="margin-bottom:16px;">
            Текущий: <span style="font-family:var(--mono);color:var(--text-2);">{{ $user->email }}</span>.
            Смена возможна только через подачу заявки и одобрение администратора.
        </p>

        @if(!$emailChange)
            <form action="{{ route('email-change.submit') }}" method="POST" class="eri-col" style="gap:12px;">
                @csrf
                <div>
                    <label class="eri-label" for="new-email">Новый email</label>
                    <input id="new-email" type="email" name="new_email" required placeholder="new@example.com" class="eri-input">
                </div>
                <div>
                    <x-eriiba.btn variant="primary" type="submit" size="sm">
                        <i class="fa-solid fa-paper-plane"></i> Подать заявку
                    </x-eriiba.btn>
                </div>
                <div class="eri-help">
                    На указанный адрес будет отправлен код подтверждения. После ввода кода заявка попадёт администратору на одобрение.
                </div>
            </form>
        @elseif($emailChange->status === 'pending_code')
            <div class="eri-alert warn" style="margin-bottom:12px;">
                <i class="fa-solid fa-envelope"></i>
                На <strong style="font-family:var(--mono);">{{ $emailChange->new_email }}</strong> отправлен код подтверждения. Введите его ниже.
            </div>
            <form action="{{ route('email-change.verify') }}" method="POST" class="eri-row" style="gap:10px;align-items:flex-end;">
                @csrf
                <div style="flex:1;">
                    <label class="eri-label" for="code-input">Код из письма</label>
                    <input id="code-input" type="text" name="code" required pattern="[0-9]{6}" maxlength="6"
                           placeholder="000000" class="eri-input"
                           style="font-family:var(--mono);letter-spacing:.4em;text-align:center;">
                </div>
                <x-eriiba.btn variant="primary" type="submit">Подтвердить</x-eriiba.btn>
            </form>
            <form action="{{ route('email-change.cancel') }}" method="POST" style="margin-top:8px;">
                @csrf
                <button type="submit" class="eri-btn ghost sm" style="color:var(--text-3);">
                    Отменить заявку
                </button>
            </form>
        @elseif($emailChange->status === 'awaiting_admin')
            <div class="eri-alert" style="border-color:var(--accent);background:var(--accent-soft);color:var(--accent);">
                <i class="fa-solid fa-clock"></i>
                Заявка на смену почты на <strong style="font-family:var(--mono);">{{ $emailChange->new_email }}</strong>
                ожидает одобрения администратора.
            </div>
            <form action="{{ route('email-change.cancel') }}" method="POST" style="margin-top:8px;">
                @csrf
                <button type="submit" class="eri-btn ghost sm" style="color:var(--text-3);">
                    Отменить заявку
                </button>
            </form>
        @endif
    </div>
</div>
