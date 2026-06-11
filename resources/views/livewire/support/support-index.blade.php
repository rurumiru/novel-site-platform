<div class="eri-section support-page">
    <x-eriiba.section-head title="Поддержка" sub="Задайте вопрос или сообщите о проблеме — ответим в течение суток." />

    @if(session('flash_success'))
        <div class="support-flash ok">
            <i class="fa-solid fa-circle-check"></i> {{ session('flash_success') }}
        </div>
    @endif

    @guest
        <div class="support-auth-wall">
            <i class="fa-solid fa-lock" style="font-size:32px;color:var(--text-muted);"></i>
            <p>Чтобы создать заявку, нужно войти в аккаунт.</p>
            <a href="{{ route('login') }}" wire:navigate class="eri-btn primary">Войти</a>
        </div>
    @else

    <div class="support-tabs">
        <button type="button" wire:click="$set('tab','list')"   class="support-tab {{ $tab==='list'   ? 'on' : '' }}">
            <i class="fa-solid fa-inbox"></i>
            {{ $isStaff ? 'Все заявки' : 'Мои заявки' }}
        </button>
        <button type="button" wire:click="$set('tab','create')" class="support-tab {{ $tab==='create' ? 'on' : '' }}">
            <i class="fa-solid fa-plus"></i> Новая заявка
        </button>
    </div>

    @if($tab === 'create')
    <div class="support-form-wrap">
        <form wire:submit="submit" class="support-form">
            <div class="support-form-row">
                <label class="support-label">Тип заявки</label>
                <div class="support-cat-seg">
                    @foreach(['reader' => 'Читатель', 'author' => 'Автор', 'bug' => 'Ошибка', 'other' => 'Другое'] as $val => $label)
                        <button type="button" wire:click="$set('category','{{ $val }}')"
                            class="support-cat-btn {{ $category === $val ? 'on' : '' }}">{{ $label }}</button>
                    @endforeach
                </div>
                @error('category') <span class="support-err">{{ $message }}</span> @enderror
            </div>

            <div class="support-form-row">
                <label class="support-label" for="sup-subject">Тема</label>
                <input id="sup-subject" type="text" wire:model="subject" placeholder="Кратко опишите проблему…" class="support-input" maxlength="255">
                @error('subject') <span class="support-err">{{ $message }}</span> @enderror
            </div>

            <div class="support-form-row">
                <label class="support-label" for="sup-body">Подробности</label>
                <textarea id="sup-body" wire:model="body" placeholder="Опишите подробнее: что произошло, что ожидалось…" class="support-textarea" rows="6" maxlength="5000"></textarea>
                <div class="support-charcount">{{ mb_strlen($body) }} / 5000</div>
                @error('body') <span class="support-err">{{ $message }}</span> @enderror
            </div>

            <div class="support-form-foot">
                <button type="submit" class="eri-btn primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit"><i class="fa-solid fa-paper-plane"></i> Отправить</span>
                    <span wire:loading wire:target="submit"><i class="fa-solid fa-circle-notch fa-spin"></i> Отправка…</span>
                </button>
                <button type="button" wire:click="$set('tab','list')" class="eri-btn ghost">Отмена</button>
            </div>
        </form>
    </div>
    @endif

    @if($tab === 'list')
        @if($tickets->isEmpty())
            <div class="support-empty">
                <i class="fa-solid fa-ticket"></i>
                <p>Заявок пока нет.</p>
                <button type="button" wire:click="$set('tab','create')" class="eri-btn primary sm">
                    <i class="fa-solid fa-plus"></i> Создать заявку
                </button>
            </div>
        @else
        <div class="support-list">
            @foreach($tickets as $ticket)
            @php
                $ageH  = $ticket->ageHours;
                $pct   = $ticket->agePercent;
                $hue   = max(0, 120 - (int)($pct * 1.2));
            @endphp
            <a href="{{ route('support.show', $ticket->id) }}" wire:navigate class="support-item status-{{ $ticket->status }}">
                
                @if($ticket->status === 'open')
                <div class="support-sla-bar">
                    <div class="support-sla-fill" style="width:{{ $pct }}%;background:hsl({{ $hue }},65%,42%);"></div>
                </div>
                @endif

                <div class="support-item-main">
                    <div class="support-item-head">
                        <span class="support-badge-cat cat-{{ $ticket->category }}">{{ $ticket->categoryLabel }}</span>
                        <span class="support-badge-status st-{{ $ticket->status }}">{{ $ticket->statusLabel }}</span>
                        @if($isStaff && $ticket->status === 'open')
                            <span class="support-age-label" style="color:hsl({{ $hue }},60%,40%);">
                                <i class="fa-regular fa-clock"></i>
                                @if($ageH < 1)
                                    только что
                                @elseif($ageH < 24)
                                    {{ round($ageH) }}ч назад
                                @else
                                    {{ round($ageH / 24) }}д назад
                                @endif
                            </span>
                        @endif
                    </div>
                    <div class="support-item-subject">{{ $ticket->subject }}</div>
                    <div class="support-item-meta">
                        @if($isStaff)
                            <span><i class="fa-solid fa-user"></i> {{ $ticket->user?->name ?? '—' }}</span> &bull;
                        @endif
                        <span>{{ $ticket->created_at->diffForHumans() }}</span>
                        @if($ticket->replies_count)
                            &bull; <span><i class="fa-regular fa-comment"></i> {{ $ticket->replies_count }}</span>
                        @endif
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right support-item-arrow"></i>
            </a>
            @endforeach
        </div>
        <div class="support-pagination">{{ $tickets->links() }}</div>
        @endif
    @endif

    @endguest
</div>
