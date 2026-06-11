@extends('layouts.app')
@section('title', $novel->title)

@section('content')
@if(\App\Models\Setting::retrieve('enable_new_design', '1') == '1')
    
    <div class="relative h-[400px] w-full overflow-hidden">
        @if($novel->background_image) <img src="/storage/{{ $novel->background_image }}" class="w-full h-full object-cover">
        @elseif($novel->cover_image) <img src="/storage/{{ $novel->cover_image }}" class="w-full h-full object-cover blur-3xl opacity-40 scale-110">
        @else <div class="w-full h-full bg-slate-900"></div> @endif
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-slate-50 dark:to-slate-950"></div>

        <div class="absolute bottom-0 left-0 right-0 p-4 md:p-12 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row gap-8 items-end">
                
                <div class="w-40 md:w-56 flex-shrink-0 rounded-xl overflow-hidden shadow-2xl border-4 border-white dark:border-slate-800 relative">
                    @if($novel->cover_image) <img src="/storage/{{ $novel->cover_image }}" class="w-full h-full object-cover"> @endif
                    @if($novel->is_adult) <div class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">18+</div> @endif
                </div>

                <div class="flex-grow text-white mb-2">
                    <h1 class="text-3xl md:text-5xl font-extrabold mb-4 drop-shadow-lg leading-tight">{{ $novel->title }}</h1>
                    
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($novel->tags as $tag)
                            <span class="px-2 py-1 bg-white/20 backdrop-blur rounded text-xs font-bold border border-white/30">{{ $tag->name }}</span>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-6 text-sm font-medium text-slate-200">
                        <div class="flex items-center gap-2">
                            <img src="{{ $novel->author->avatar_url }}" class="w-6 h-6 rounded-full border border-white/50">
                            <span>Опубликовал: <a href="{{ route('users.show', $novel->author->id) }}" class="text-white hover:underline">{{ $novel->author->name }}</a></span>
                        </div>
                        @if($novel->author_name) <span>Автор: <span class="text-white">{{ $novel->author_name }}</span></span> @endif
                        @if($novel->original_author) <span>Оригинал: <span class="text-white">{{ $novel->original_author }}</span></span> @endif
                        @if($novel->source_link) <a href="{{ $novel->source_link }}" target="_blank" class="text-indigo-300 hover:text-white transition"><i class="fa-solid fa-link"></i> Источник</a> @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <div class="lg:col-span-2 space-y-12">
            
            <div class="flex flex-wrap gap-4">
                @if($lastRead = $novel->last_read_chapter)
                    <a href="{{ route('novel.read', [$novel->id, $lastRead->id]) }}" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold shadow-lg transition">Продолжить</a>
                @else
                    @if($first = $novel->publishedChapters->first())
                        <a href="{{ route('novel.read', [$novel->id, $first->id]) }}" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold shadow-lg transition">Начать читать</a>
                    @endif
                @endif
                @livewire('favorite-button', ['novel' => $novel])
                
                @if($novel->author && $novel->author->donation_link && $novel->author->is_donation_link_approved)
                    <a href="{{ $novel->author->donation_link }}" target="_blank" class="px-6 py-3 bg-pink-600 hover:bg-pink-500 text-white rounded-xl font-bold shadow-lg transition"><i class="fa-solid fa-heart mr-2"></i> Донат</a>
                @endif
            </div>

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-2">Описание</h3>
                <div class="prose prose-slate dark:prose-invert max-w-none">
                    {!! Str::markdown($novel->description ?? '') !!}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-2">Обсуждение</h3>
                @livewire('comments-section', ['model' => $novel])
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="font-bold text-slate-900 dark:text-white">Содержание</h3>
                        <p class="text-xs text-slate-500">{{ $novel->chapters->count() }} глав</p>
                    </div>
                    <div class="max-h-[80vh] overflow-y-auto p-2 space-y-4 custom-scrollbar">
                        @foreach($volumes as $volume)
                            @if($volume->chapters->count() > 0)
                                <div>
                                    <div class="px-2 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $volume->title }}</div>
                                    <div class="space-y-1">
                                        @foreach($volume->chapters as $chapter)
                                            @include('novel.partials.chapter-item-small', ['chapter' => $chapter])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        
                        @if($chaptersWithoutVolume->count() > 0)
                            <div>
                                @if($volumes->count() > 0) <div class="px-2 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Другое</div> @endif
                                <div class="space-y-1">
                                    @foreach($chaptersWithoutVolume as $chapter)
                                        @include('novel.partials.chapter-item-small', ['chapter' => $chapter])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    
    @include('novel.show-old')
@endif
@endsection
