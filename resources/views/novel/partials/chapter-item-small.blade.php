@php
    $isRead = \App\Models\ReadingProgress::where('user_id', Auth::id())->where('chapter_id', $chapter->id)->where('is_completed', true)->exists();
    $colorClass = $isRead ? 'text-green-600 dark:text-green-400' : ($chapter->is_locked && !$chapter->is_unlocked ? 'text-red-500' : 'text-slate-700 dark:text-slate-300');
@endphp

<a href="{{ route('novel.read', [$chapter->novel_id, $chapter->id]) }}" class="block p-2 text-sm rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition truncate {{ $colorClass }}">
    {{ $chapter->title }}
    @if($chapter->is_locked) <i class="fa-solid fa-lock text-[10px] ml-1"></i> @endif
    @if($isRead) <i class="fa-solid fa-check text-[10px] ml-1"></i> @endif
</a>
