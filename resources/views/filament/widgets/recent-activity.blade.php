<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Недавняя активность</x-slot>
        <x-slot name="description">События последних дней по проекту</x-slot>

        @if($items->isEmpty())
            <div style="padding:40px 20px; text-align:center; color:var(--eri-text-muted); font-style:italic; font-family:var(--eri-serif);">
                Пока тихо.
            </div>
        @else
            <div class="eri-admin-feed">
                @foreach($items as $it)
                    <a href="{{ url($it['href']) }}" class="eri-admin-feed-item">
                        <span class="eri-admin-feed-icon {{ $it['class'] }}">
                            <i class="fa-solid {{ $it['icon'] }}"></i>
                        </span>
                        <span class="eri-admin-feed-meta">{!! $it['text'] !!}</span>
                        <span class="eri-admin-feed-time">{{ $it['time']?->diffForHumans(null, true, true) ?? '' }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
