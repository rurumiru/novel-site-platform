<a href="{{ route('novel.show', $novel->id) }}" class="block p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-indigo-500 transition group">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-500 transition truncate">{{ $novel->title }}</h3>
    <div class="flex justify-between items-center mt-2 text-xs text-slate-500">
        <span>{{ $novel->chapters_count }} глав</span>
        <span class="text-yellow-500 font-bold">★ {{ $novel->average_rating }}</span>
    </div>
</a>
