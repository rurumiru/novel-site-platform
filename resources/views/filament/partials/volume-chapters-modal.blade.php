<div class="space-y-1 py-1 max-h-[60vh] overflow-y-auto">
    @forelse($chapters as $i => $ch)
        <div class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $i % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/40' : '' }}">
            <span class="text-xs font-mono text-gray-400 w-6 text-right flex-shrink-0">{{ $i + 1 }}</span>
            <span class="flex-1 text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $ch->title }}</span>
            @if($ch->is_locked)
                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-2 py-0.5 rounded-full flex-shrink-0">
                    🔒 {{ $ch->price > 0 ? $ch->price . ' ₽' : '—' }}
                </span>
            @else
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex-shrink-0">Free</span>
            @endif
            @if(!$ch->is_published)
                <span class="text-xs text-gray-400 flex-shrink-0">скрыта</span>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-400 text-sm">Глав в этом томе нет</div>
    @endforelse
</div>
@if($chapters->isNotEmpty())
    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
        Всего: {{ $chapters->count() }} гл. ·
        Платных: {{ $chapters->where('is_locked', true)->count() }} ·
        Бесплатных: {{ $chapters->where('is_locked', false)->count() }}
    </div>
@endif
