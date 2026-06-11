<a href="{{ route('novel.show', $novel->id) }}" class="flex items-start p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-indigo-500 transition group">
    <div class="w-20 h-28 bg-slate-200 dark:bg-slate-800 rounded-lg overflow-hidden flex-shrink-0 mr-4">
        @if($novel->cover_image) <img src="{{ \App\Models\Novel::storageUrl($novel->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition"> @endif
    </div>
    <div class="flex-grow min-w-0">
        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-500 transition truncate">{{ $novel->title }}</h3>
        <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
            <span class="text-yellow-500 font-bold">★ {{ $novel->average_rating }}</span>
            <span>• {{ $novel->chapters_count }} глав</span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">{{ strip_tags($novel->description) }}</p>
    </div>
</a>
