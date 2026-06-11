@php
    $isRead = \App\Models\ReadingProgress::where('user_id', Auth::id())->where('chapter_id', $chapter->id)->where('is_completed', true)->exists();
    $progress = \App\Models\ReadingProgress::where('user_id', Auth::id())->where('chapter_id', $chapter->id)->value('percent') ?? 0;
    
    $bgClass = 'bg-slate-50 dark:bg-slate-800';
    if ($isRead) $bgClass = 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
    elseif ($chapter->is_locked && !$chapter->is_unlocked) $bgClass = 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800';
@endphp

<a href="{{ route('novel.read', [$chapter->novel_id, $chapter->id]) }}" 
   class="flex justify-between items-center p-4 rounded-xl border transition group relative overflow-hidden {{ $bgClass }} hover:border-indigo-500 dark:hover:border-indigo-500">

    @if($progress > 0 && !$isRead)
        <div class="absolute bottom-0 left-0 h-1 bg-indigo-500" style="width: {{ $progress }}%"></div>
    @endif

    <span class="text-slate-700 dark:text-slate-200 font-medium group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition flex items-center z-10">
        @if($chapter->is_locked) 
            <i class="fa-solid {{ $chapter->is_unlocked ? 'fa-unlock text-green-500' : 'fa-lock text-red-400' }} mr-2"></i> 
        @endif
        
        @if($isRead)
            <i class="fa-solid fa-check text-green-500 mr-2 text-xs"></i>
        @endif

        {{ $chapter->title }}
    </span>

    <div class="flex flex-col items-end z-10">
        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $chapter->created_at->format('d.m') }}</span>
    </div>
</a>
