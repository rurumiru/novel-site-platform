@php
    $statusMap = [
        'ongoing'   => ['label' => 'Выходит',   'cls' => 'text-emerald-500 dark:text-emerald-400'],
        'completed' => ['label' => 'Завершён',   'cls' => 'text-sky-500 dark:text-sky-400'],
        'hiatus'    => ['label' => 'Заморожен',  'cls' => 'text-amber-500 dark:text-amber-400'],
    ];
    $st = $statusMap[$novel->status] ?? ['label' => $novel->status, 'cls' => 'text-slate-400'];
@endphp
<a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="group block" aria-label="{{ $novel->title }}">
    <div class="relative aspect-[2/3] rounded-xl overflow-hidden mb-2 shadow-sm group-hover:shadow-lg transition-all duration-300 bg-slate-200 dark:bg-slate-800">
        @if($novel->cover_image)
            <img src="{{ \App\Models\Novel::storageUrl($novel->cover_image) }}"
                 alt="Обложка {{ $novel->title }}"
                 loading="lazy"
                 decoding="async"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        @else
            <div class="flex h-full items-center justify-center text-slate-400 dark:text-slate-600"><i class="fa-solid fa-book text-3xl"></i></div>
        @endif

        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded-full text-[10px] text-amber-400 font-bold flex items-center gap-0.5">
            <i class="fa-solid fa-star text-[8px]"></i>{{ $novel->average_rating }}
        </div>
    </div>
    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">{{ $novel->title }}</h3>
    <p class="text-[10px] {{ $st['cls'] }} font-bold uppercase tracking-wide mt-0.5">{{ $st['label'] }}</p>
</a>
