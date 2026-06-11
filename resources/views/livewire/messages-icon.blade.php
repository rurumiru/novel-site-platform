<a href="{{ route('messages') }}" wire:navigate title="Сообщения"
   style="position:relative;display:inline-grid;place-items:center;width:40px;height:40px;border-radius:var(--r-pill);color:var(--text-3);text-decoration:none;transition:background 0.15s,color 0.15s;"
   onmouseover="this.style.background='var(--surface-2)';this.style.color='var(--text)'"
   onmouseout="this.style.background='transparent';this.style.color='var(--text-3)'">
    <i class="fa-regular fa-comment-dots" style="font-size:18px;"></i>
    @if($unreadCount > 0)
        <span style="position:absolute;top:2px;right:2px;min-width:18px;height:18px;padding:0 5px;display:inline-flex;align-items:center;justify-content:center;background:var(--err);color:#fff;font-size:10px;font-weight:700;border-radius:var(--r-pill);font-family:var(--sans);">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
    @endif
</a>
