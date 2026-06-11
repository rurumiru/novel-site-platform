<a href="{{ route('notifications') }}" wire:navigate title="Уведомления"
   style="position:relative;display:inline-grid;place-items:center;width:40px;height:40px;border-radius:var(--r-pill);color:var(--text-3);text-decoration:none;transition:background 0.15s,color 0.15s;"
   onmouseover="this.style.background='var(--surface-2)';this.style.color='var(--text)'"
   onmouseout="this.style.background='transparent';this.style.color='var(--text-3)'">
    <i class="fa-regular fa-bell" style="font-size:18px;"></i>
    @if($unreadCount > 0)
        <span style="position:absolute;top:7px;right:8px;width:8px;height:8px;background:var(--err);border-radius:50%;border:2px solid var(--surface);box-shadow:0 0 0 1px var(--err);"></span>
    @endif
</a>
