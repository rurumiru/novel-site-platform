<div class="editor-shell">
    <div class="editor-head">
        <h1>Мои работы</h1>
        <div class="spacer"></div>
        @if($canCreate)
            <x-eriiba.btn variant="primary" wire:click="create" icon="plus">Создать</x-eriiba.btn>
        @endif
    </div>

    @if($novels->isEmpty())
        <div class="eri-card eri-card-pad-lg" style="text-align:center; color:var(--text-muted); padding:60px 16px;">Нет работ</div>
    @else
        <div class="lib-list">
            @foreach($novels as $novel)
                <div class="eri-card eri-card-pad-lg" style="display:flex; flex-direction:column; gap:24px;">
                    <div style="display:flex; flex-wrap:wrap; gap:24px;">
                        
                        <div style="width:180px; flex-shrink:0; display:flex; flex-direction:column; gap:12px;">
                            <div style="width:100%; aspect-ratio:2/3; background:var(--surface-2); border-radius:var(--r-sm); overflow:hidden; position:relative;">
                                @if($novel->cover_image)
                                    <img src="{{ \App\Models\Novel::storageUrl($novel->cover_image) }}" alt="{{ $novel->title }}" style="width:100%; height:100%; object-fit:cover;">
                                @endif
                                <span style="position:absolute; top:8px; left:8px;">
                                    <x-eriiba.chip :variant="$novel->is_published ? 'accent' : 'warn'">{{ $novel->is_published ? 'Опубликовано' : 'Черновик' }}</x-eriiba.chip>
                                </span>
                            </div>
                            <div class="editor-grid cols-2" style="gap:6px;">
                                <x-eriiba.btn size="sm" :href="route('author.novel.edit', $novel->id)">Настройки</x-eriiba.btn>
                                <x-eriiba.btn size="sm" :href="route('author.novel.stats', $novel->id)">Статистика</x-eriiba.btn>
                            </div>
                            <x-eriiba.btn variant="danger" size="sm" block wire:click="delete({{ $novel->id }})" wire:confirm="Вы уверены?">Удалить</x-eriiba.btn>
                        </div>

                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
                                <div style="min-width:0;">
                                    <h2 style="font-family:var(--display); font-size:22px; font-weight:500; color:var(--text); margin:0 0 6px;">
                                        <a href="{{ route('novel.show', $novel->id) }}" style="color:inherit; text-decoration:none;">{{ $novel->title }}</a>
                                    </h2>
                                    <div style="display:flex; flex-wrap:wrap; gap:14px; font-size:12px; color:var(--text-muted);">
                                        <span><i class="fa-solid fa-layer-group"></i> {{ $novel->chapters_count }} глав</span>
                                        <span><i class="fa-solid fa-eye"></i> {{ $novel->views }}</span>
                                        <span><i class="fa-solid fa-thumbs-up"></i> {{ $novel->total_likes }}</span>
                                        <span><i class="fa-solid fa-star" style="color:var(--warn);"></i> {{ $novel->average_rating }}</span>
                                    </div>
                                </div>
                                <x-eriiba.btn variant="primary" size="sm" :href="route('author.chapter.create', $novel->id)" icon="plus">Глава</x-eriiba.btn>
                            </div>

                            <div class="eri-card" style="padding:14px; background:var(--surface-2);">
                                <div class="eri-label" style="margin-bottom:10px;">Последние главы</div>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    @foreach($novel->chapters()->latest()->take(3)->get() as $chapter)
                                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; font-size:13px;">
                                            <span style="color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $chapter->title }}</span>
                                            <span style="color:var(--text-muted); font-size:11px; white-space:nowrap;">{{ $chapter->created_at->format('d.m.Y') }}</span>
                                        </div>
                                    @endforeach
                                    @if($novel->chapters_count == 0)
                                        <p style="color:var(--text-muted); font-size:12px; font-style:italic; margin:0;">Глав пока нет</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
