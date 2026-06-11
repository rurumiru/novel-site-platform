<button wire:click="toggle" class="eri-btn block"
        style="{{ $isFavorited ? 'background:var(--tag-romance-bg);color:var(--tag-romance-fg);border-color:transparent;' : '' }}">
    <i class="{{ $isFavorited ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
    <span>{{ $isFavorited ? 'В избранном' : 'В избранное' }}</span>
</button>
