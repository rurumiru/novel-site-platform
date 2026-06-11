<div x-data="{ active: 0, count: {{ $banners->count() }}, timer: null, next() { this.active = (this.active + 1) % this.count; } }" 
     x-init="timer = setInterval(() => next(), 6000)"
     class="relative w-full h-[400px] md:h-[500px] overflow-hidden rounded-3xl mx-auto max-w-[95%] shadow-xl mt-4 bg-slate-200 dark:bg-slate-800">
    @foreach($banners as $index => $banner)
    <div x-show="active === {{ $index }}" x-transition.opacity.duration.1000ms class="absolute inset-0">
        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async" width="1280" height="720" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
        <div class="absolute bottom-0 left-0 p-8 md:p-16 text-white max-w-3xl">
            <h2 class="text-3xl md:text-5xl font-bold mb-2">{{ $banner->title }}</h2>
            
            <p class="text-sm md:text-lg text-gray-200 mb-6 line-clamp-2 md:line-clamp-3">{{ $banner->description }}</p>
            
            @if($banner->link) <a href="{{ $banner->link }}" wire:navigate class="px-8 py-3 bg-white text-black rounded-full font-bold hover:bg-indigo-50 transition inline-block">Читать</a> @endif
        </div>
    </div>
    @endforeach
</div>
