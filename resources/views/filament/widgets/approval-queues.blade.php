<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Очереди на проверку</x-slot>
        <x-slot name="description">{{ $totalPending > 0 ? $totalPending . ' элементов ждут вашей реакции' : 'Всё разобрано — очередей нет' }}</x-slot>

        <div class="eri-queues-grid">
            @foreach($queues as $q)
                <a href="{{ $q['href'] }}" class="eri-queue-card {{ $q['count'] > 0 ? 'has-pending' : '' }}">
                    <span class="eri-queue-icon">
                        <i class="fa-solid {{ $q['icon'] }}"></i>
                    </span>
                    <div class="eri-queue-meta">
                        <div class="eri-queue-count">{{ number_format($q['count'], 0, '.', ' ') }}</div>
                        <div class="eri-queue-label">{{ $q['label'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
