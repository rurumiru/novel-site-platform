<button wire:click="toggle"
        style="background:none;border:0;cursor:pointer;display:inline-flex;flex-direction:column;align-items:center;gap:2px;padding:6px 10px;border-radius:var(--r-sm);font-family:inherit;color:{{ $isLiked ? 'var(--err)' : 'var(--text-muted)' }};transition:background 0.15s;"
        onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
    <i class="{{ $isLiked ? 'fa-solid' : 'fa-regular' }} fa-heart" style="font-size:20px;"></i>
    <span style="font-size:11px;font-weight:700;font-family:var(--mono);">{{ $count }}</span>
</button>
