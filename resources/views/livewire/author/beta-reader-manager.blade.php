<div style="display:flex; flex-direction:column; gap:14px;">
    @if($feedback)
        <div class="eri-alert {{ $feedbackType === 'success' ? 'ok' : '' }}">{{ $feedback }}</div>
    @endif

    <div style="position:relative;" x-data="{ open: false }">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Поиск по имени или email…"
               @focus="open = true" @click.away="open = false"
               class="eri-input">

        @if(count($searchResults))
        <div class="eri-card" style="position:absolute; top:100%; left:0; right:0; margin-top:4px; padding:0; box-shadow:var(--shadow); z-index:20; overflow:hidden;">
            @foreach($searchResults as $u)
            <button type="button" wire:click="addBetaReader({{ $u['id'] }})"
                    style="width:100%; display:flex; align-items:center; gap:10px; padding:8px 12px; background:none; border:none; cursor:pointer; text-align:left; color:var(--text);"
                    class="hoverable-row">
                <span style="width:28px; height:28px; border-radius:50%; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">{{ mb_strtoupper(mb_substr($u['name'], 0, 1)) }}</span>
                <div style="min-width:0;">
                    <div style="font-size:13px; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $u['name'] }}</div>
                    <div style="font-size:11px; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $u['email'] }}</div>
                </div>
                <i class="fa-solid fa-plus" style="color:var(--ok); margin-left:auto; flex-shrink:0;"></i>
            </button>
            @endforeach
        </div>
        @endif
    </div>

    @if($betaReaders->isEmpty())
        <p style="font-size:13px; color:var(--text-muted); text-align:center; padding:14px 0; margin:0;">Бета-ридеры не добавлены</p>
    @else
        <div style="display:flex; flex-direction:column; gap:6px;">
            @foreach($betaReaders as $br)
            <div class="eri-card" style="display:flex; align-items:center; gap:10px; padding:8px 12px;">
                <span style="width:28px; height:28px; border-radius:50%; background:color-mix(in srgb, var(--accent) 16%, transparent); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">{{ mb_strtoupper(mb_substr($br->user->name ?? '?', 0, 1)) }}</span>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $br->user->name ?? '—' }}</div>
                    <div style="font-size:11px; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $br->user->email ?? '' }}</div>
                </div>
                <button type="button" wire:click="removeBetaReader({{ $br->user_id }})" style="background:none; border:none; color:var(--text-muted); cursor:pointer; flex-shrink:0;" title="Удалить">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            @endforeach
        </div>
    @endif

    @if($notes->isNotEmpty())
    <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--border);">
        <div class="eri-label" style="margin-bottom:8px;">Заметки читателей</div>
        <div style="display:flex; flex-direction:column; gap:8px; max-height:200px; overflow-y:auto;">
            @foreach($notes as $note)
            <div class="eri-card" style="padding:10px; background:color-mix(in srgb, var(--accent) 6%, transparent);">
                <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                    <strong style="font-size:12px; color:var(--accent);">{{ $note->user->name ?? '—' }}</strong>
                    @if($note->chapter)
                        <span style="color:var(--text-muted);">·</span>
                        <span style="font-size:11px; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $note->chapter->title }}</span>
                    @endif
                    <span style="margin-left:auto; font-size:10px; color:var(--text-muted); flex-shrink:0;">{{ $note->created_at->diffForHumans() }}</span>
                </div>
                <p style="color:var(--text); font-size:12px; line-height:1.5; margin:0;">{{ $note->content }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
