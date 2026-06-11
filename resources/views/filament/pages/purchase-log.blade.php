<x-filament-panels::page>
    <div class="space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Покупки глав</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($chapterRevenue, 0, '.', ' ') }} ₽</p>
                <p class="text-xs text-gray-400 mt-1">{{ $chapterCount }} транзакций</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Подписки (активные)</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($subsRevenue, 0, '.', ' ') }} ₽</p>
                <p class="text-xs text-gray-400 mt-1">{{ $subsCount }} активных · {{ $pendingCount }} ожидают</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Общий оборот</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($chapterRevenue + $subsRevenue, 0, '.', ' ') }} ₽</p>
                <p class="text-xs text-gray-400 mt-1">главы + подписки</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 dark:text-white text-sm">Последние покупки глав</h2>
                    <a href="{{ route('filament.admin.resources.chapter-purchases.index') }}"
                       class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Все записи →</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentChapters as $p)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $p->user?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $p->chapter?->title ?? '—' }} · {{ $p->chapter?->novel?->title ?? '—' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $p->price_paid }} ₽</p>
                                <p class="text-[10px] text-gray-400">{{ $p->created_at?->format('d.m H:i') ?? '—' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-gray-400 py-8">Покупок пока нет</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 dark:text-white text-sm">Последние подписки</h2>
                    <a href="{{ route('filament.admin.resources.subscriptions.index') }}"
                       class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Все записи →</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentSubs as $s)
                        @php
                            $color = match($s->status) {
                                'active'   => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'pending'  => 'text-amber-600 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-400',
                                'rejected' => 'text-red-600 bg-red-50 dark:bg-red-900/30 dark:text-red-400',
                                default    => 'text-gray-500 bg-gray-100 dark:bg-gray-800',
                            };
                            $label = match($s->status) {
                                'active'   => 'Активна',
                                'pending'  => 'Ожидает',
                                'rejected' => 'Отклонена',
                                default    => $s->status,
                            };
                        @endphp
                        <div class="flex items-center gap-3 px-5 py-3">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $s->user?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ $s->type === 'author_bundle' ? 'Пакет автора' : ($s->novel?->title ?? '—') }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0 space-y-0.5">
                                <p class="text-sm font-black text-gray-900 dark:text-white">{{ $s->amount_paid }} ₽</p>
                                <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded {{ $color }}">{{ $label }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-gray-400 py-8">Подписок пока нет</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page>
