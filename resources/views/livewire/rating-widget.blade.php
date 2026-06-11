<div style="display:inline-flex;align-items:center;gap:4px;">
    @for($i = 1; $i <= 5; $i++)
        <button wire:click="rate({{ $i }})"
                style="background:none;border:0;cursor:pointer;padding:2px;color:{{ $i <= $myRating ? 'var(--gold)' : 'var(--text-faint)' }};transition:transform 0.12s;font-family:inherit;"
                onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
            <i class="{{ $i <= $myRating ? 'fa-solid' : 'fa-regular' }} fa-star" style="font-size:22px;"></i>
        </button>
    @endfor
    <span style="margin-left:8px;font-size:13px;color:var(--text-3);font-family:var(--mono);">({{ $novel->average_rating }})</span>
</div>
