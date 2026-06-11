@if($chapter->published_at > now())
    
    <div class="flex justify-between items-center p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 opacity-70 cursor-not-allowed"
         x-data="{ 
             time: {{ $chapter->published_at->timestamp }} * 1000, 
             now: new Date().getTime(),
             countdown: '',
             update() {
                 const diff = this.time - new Date().getTime();
                 if (diff <= 0) { this.countdown = 'Доступно!'; return; }
                 const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                 const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                 const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                 this.countdown = d + 'д ' + h + 'ч ' + m + 'м';
             }
         }"
         x-init="update(); setInterval(() => update(), 60000)">
        <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center">
            <i class="fa-regular fa-clock mr-2"></i>
            <span class="text-sm">{{ $chapter->title }}</span>
        </span>
        <span class="text-xs font-bold text-indigo-500 bg-indigo-100 dark:bg-indigo-900/30 px-2 py-1 rounded" x-text="countdown"></span>
    </div>
@else
    
    <a href="{{ route('novel.read', [$chapter->novel_id, $chapter->id]) }}" class="flex justify-between items-center p-4 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-slate-700 border border-slate-100 dark:border-slate-700 transition group">
        <span class="text-slate-700 dark:text-slate-200 font-medium group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition flex items-center">
            @if($chapter->is_locked) <i class="fa-solid fa-lock mr-2 text-red-400"></i> @endif
            {{ $chapter->title }}
        </span>
        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $chapter->created_at->format('d.m') }}</span>
    </a>
@endif
