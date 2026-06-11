<div class="eri-card" style="display:flex; justify-content:space-between; align-items:center; padding:12px;">
    <div style="display:flex; align-items:center; gap:10px;">
        <strong style="color:var(--text);">{{ $chapter->title }}</strong>

        @if(!$chapter->is_published)
            <x-eriiba.chip variant="warn">Черновик</x-eriiba.chip>
        @endif

        @if($chapter->is_locked)
            <span style="font-size:16px;" title="Закрытая глава">🔒</span>
        @endif

        @if($chapter->published_at && $chapter->published_at->isFuture())
            <x-eriiba.chip variant="accent" title="Запланировано">
                <i class="fa-solid fa-clock"></i> {{ $chapter->published_at->format('d.m H:i') }}
            </x-eriiba.chip>
        @endif
    </div>
    <a href="{{ route('author.chapter.edit', [$chapter->novel_id, $chapter->id]) }}" style="color:var(--accent); text-decoration:none; font-size:13px; font-weight:600;">Редактировать</a>
</div>
