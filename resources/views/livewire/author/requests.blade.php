<div class="editor-shell">
    <div class="editor-head">
        <h1>Входящие заявки</h1>
        <div class="spacer"></div>
        <x-eriiba.btn :href="route('my-novels')" wire:navigate icon="arrow-left">Назад к работам</x-eriiba.btn>
    </div>

    <div class="editor-grid" style="gap:12px;">
        @forelse($requests as $req)
            <div class="eri-card eri-card-pad-lg" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center; justify-content:space-between;">
                <div style="display:flex; gap:14px; align-items:center; min-width:0;">
                    <img src="{{ $req->user->avatar_url }}" alt="" style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:1px solid var(--border);">
                    <div style="min-width:0;">
                        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                            <strong style="color:var(--text);">{{ $req->user->name }}</strong>
                            <x-eriiba.chip>UID: {{ $req->uid }}</x-eriiba.chip>
                        </div>
                        <div style="margin-top:6px; color:var(--text-muted); font-size:13px;">
                            @if($req->type === 'author_bundle')
                                <x-eriiba.chip variant="accent"><i class="fa-solid fa-layer-group"></i> Пакет «Всё включено»</x-eriiba.chip>
                            @else
                                Покупка: <strong style="color:var(--text);">{{ $req->novel->title ?? '—' }}</strong>
                            @endif
                        </div>
                        @if($req->user->social_link)
                            <a href="{{ $req->user->social_link }}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; margin-top:6px; font-size:12px; color:var(--accent);">
                                <i class="fa-brands fa-telegram"></i> Связаться с покупателем
                            </a>
                        @endif
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:10px;">
                    <strong style="color:var(--text); font-size:18px;">{{ number_format($req->amount_paid, 0) }} ₽</strong>
                    <x-eriiba.btn variant="primary" wire:click="approve({{ $req->id }})" icon="check">Подтвердить</x-eriiba.btn>
                    <x-eriiba.btn variant="danger" wire:click="reject({{ $req->id }})" icon="xmark">Отклонить</x-eriiba.btn>
                </div>
            </div>
        @empty
            <div class="eri-card eri-card-pad-lg" style="text-align:center; color:var(--text-muted);">
                <div style="font-size:32px; margin-bottom:8px;"><i class="fa-regular fa-envelope-open"></i></div>
                <p>Нет новых заявок на подписку.</p>
            </div>
        @endforelse
    </div>
</div>
