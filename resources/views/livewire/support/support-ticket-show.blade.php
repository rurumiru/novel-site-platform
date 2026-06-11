<div class="eri-section support-page">
    
    <nav class="support-breadcrumb">
        <a href="{{ route('support.index') }}" wire:navigate><i class="fa-solid fa-inbox"></i> Поддержка</a>
        <span>/</span>
        <span>Заявка #{{ $ticket->id }}</span>
    </nav>

    <div class="support-ticket-header">
        <div class="support-ticket-title-row">
            <span class="support-badge-cat cat-{{ $ticket->category }}">{{ $ticket->categoryLabel }}</span>
            <h1 class="support-ticket-title">{{ $ticket->subject }}</h1>
        </div>
        <div class="support-ticket-meta">
            <span><i class="fa-solid fa-user"></i> {{ $ticket->user?->name ?? '—' }}</span>
            <span><i class="fa-regular fa-clock"></i> {{ $ticket->created_at->diffForHumans() }}</span>
            <span class="support-badge-status st-{{ $ticket->status }}">{{ $ticket->statusLabel }}</span>

            @if($isStaff)
            <div class="support-status-controls">
                @foreach(['open' => ['Открыта', 'fa-lock-open'], 'answered' => ['Отвечено', 'fa-check'], 'closed' => ['Закрыта', 'fa-lock']] as $st => [$label, $icon])
                    <button type="button"
                        wire:click="changeStatus('{{ $st }}')"
                        class="eri-btn sm {{ $ticket->status === $st ? 'primary' : 'ghost' }}">
                        <i class="fa-solid {{ $icon }}"></i> {{ $label }}
                    </button>
                @endforeach
            </div>
            @endif
        </div>

        @if($ticket->status === 'open')
        @php
            $pct = $ticket->agePercent;
            $hue = max(0, 120 - (int)($pct * 1.2));
        @endphp
        <div class="support-sla-full">
            <div class="support-sla-labels">
                <span>Время ответа</span>
                <span style="color:hsl({{ $hue }},60%,40%);">
                    @php $h = $ticket->ageHours; @endphp
                    @if($h < 1) менее часа
                    @elseif($h < 24) {{ round($h) }}ч из 24ч
                    @else <strong>Просрочено ({{ round($h) }}ч)</strong>
                    @endif
                </span>
            </div>
            <div class="support-sla-track">
                <div class="support-sla-fill" style="width:{{ $pct }}%;background:hsl({{ $hue }},65%,42%);"></div>
            </div>
        </div>
        @endif
    </div>

    <div class="support-message origin">
        <div class="support-msg-avatar">
            @if($ticket->user?->avatar)
                <img src="{{ $ticket->user->avatar }}" alt="{{ $ticket->user->name }}" class="eri-avatar" style="width:38px;height:38px;">
            @else
                <div class="eri-avatar" style="width:38px;height:38px;">{{ mb_strtoupper(mb_substr($ticket->user?->name ?? '?', 0, 1)) }}</div>
            @endif
        </div>
        <div class="support-msg-body">
            <div class="support-msg-author">
                {{ $ticket->user?->name ?? '—' }}
                <span class="support-msg-time">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
            </div>
            <div class="support-msg-text">{{ $ticket->body }}</div>
        </div>
    </div>

    @foreach($ticket->replies as $reply)
    <div class="support-message {{ $reply->is_staff ? 'staff' : 'user-reply' }}">
        <div class="support-msg-avatar">
            @if($reply->user?->avatar)
                <img src="{{ $reply->user->avatar }}" alt="{{ $reply->user->name }}" class="eri-avatar" style="width:38px;height:38px;">
            @else
                <div class="eri-avatar" style="width:38px;height:38px;">{{ mb_strtoupper(mb_substr($reply->user?->name ?? '?', 0, 1)) }}</div>
            @endif
        </div>
        <div class="support-msg-body">
            <div class="support-msg-author">
                {{ $reply->user?->name ?? '—' }}
                @if($reply->is_staff)
                    <span class="support-staff-badge"><i class="fa-solid fa-shield-halved"></i> Поддержка</span>
                @endif
                <span class="support-msg-time">{{ $reply->created_at->format('d.m.Y H:i') }}</span>
            </div>
            <div class="support-msg-text">{{ $reply->body }}</div>
        </div>
    </div>
    @endforeach

    @auth
    @if($ticket->status !== 'closed' || $isStaff)
    <div class="support-reply-form">
        <h3 class="support-reply-title">
            @if($ticket->status === 'closed' && $isStaff)
                Ответить (заявка закрыта, только для поддержки)
            @else
                Ответить
            @endif
        </h3>
        <form wire:submit="sendReply">
            <textarea wire:model="replyBody" placeholder="Ваш ответ…" class="support-textarea" rows="4" maxlength="5000"></textarea>
            @error('replyBody') <span class="support-err">{{ $message }}</span> @enderror
            <div class="support-form-foot" style="margin-top:12px;">
                <button type="submit" class="eri-btn primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="sendReply"><i class="fa-solid fa-reply"></i> Отправить</span>
                    <span wire:loading wire:target="sendReply"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                </button>
                <a href="{{ route('support.index') }}" wire:navigate class="eri-btn ghost">← Назад</a>
            </div>
        </form>
    </div>
    @else
    <div class="support-closed-note">
        <i class="fa-solid fa-lock"></i> Заявка закрыта. <a href="{{ route('support.index') }}" wire:navigate>Создать новую</a>
    </div>
    @endif
    @endauth
</div>
