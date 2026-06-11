@php
    $novelForSidebar = $novel ?? null;
    if ($novel) {
        $totalChapters = $novel->chapters()->count();
        $publishedChapters = $novel->chapters()->where('is_published', true)->count();
        $totalViews = $novel->views ?? 0;
        $avgRating = method_exists($novel, 'getAverageRatingAttribute') ? $novel->average_rating : 0;
        $progressPct = $totalChapters > 0 ? round(($publishedChapters / $totalChapters) * 100) : 0;
    }
@endphp

<div x-data="{ activeTab: '{{ $novelId ? 'settings' : 'settings' }}' }"
     x-bind:class="(activeTab === 'chapters' && {{ $novelForSidebar ? 'true' : 'false' }}) ? 'editor-with-sidebar' : 'editor-shell'">

    @if($novelForSidebar)
    <template x-if="activeTab === 'chapters'">
        <div>
            @include('livewire.author.partials.editor-sidebar', ['novel' => $novelForSidebar, 'currentChId' => null])
        </div>
    </template>
    @endif

    <div x-bind:class="(activeTab === 'chapters' && {{ $novelForSidebar ? 'true' : 'false' }}) ? 'editor-main' : ''">

        @if($novelId)
        <div class="ne-hero">
            <div class="ne-hero-cover">
                @if($cover_image && $cover_image->isPreviewable())
                    <img src="{{ $cover_image->temporaryUrl() }}" alt="">
                @elseif($existingCover)
                    <img src="{{ \App\Models\Novel::storageUrl($existingCover) }}?v={{ $novel?->updated_at?->timestamp ?? time() }}" alt="">
                @else
                    <div class="ne-hero-cover-empty"><i class="fa-solid fa-book"></i></div>
                @endif
            </div>
            <div class="ne-hero-body">
                <div class="ne-hero-meta">
                    @if($isEditor)
                        <span class="ne-hero-badge"><i class="fa-solid fa-pen-nib"></i> Вы редактор</span>
                    @endif
                    @if($novel && $novel->status === 'ongoing')   <span class="ne-hero-badge ok">● Выходит</span> @endif
                    @if($novel && $novel->status === 'completed') <span class="ne-hero-badge accent">● Завершён</span> @endif
                    @if($novel && $novel->status === 'hiatus')    <span class="ne-hero-badge warn">● Заморожен</span> @endif
                </div>
                <h1 class="ne-hero-title">{{ $title ?: 'Без названия' }}</h1>
                <div class="ne-hero-stats">
                    <div class="ne-stat">
                        <div class="ne-stat-num">{{ $totalChapters ?? 0 }}</div>
                        <div class="ne-stat-lbl">всего глав</div>
                    </div>
                    <div class="ne-stat">
                        <div class="ne-stat-num">{{ $publishedChapters ?? 0 }}</div>
                        <div class="ne-stat-lbl">опубликовано</div>
                    </div>
                    <div class="ne-stat">
                        <div class="ne-stat-num">{{ number_format($totalViews ?? 0, 0, '.', ' ') }}</div>
                        <div class="ne-stat-lbl">прочтений</div>
                    </div>
                    <div class="ne-stat">
                        <div class="ne-stat-num">{{ number_format($avgRating ?? 0, 1) }}</div>
                        <div class="ne-stat-lbl">★ рейтинг</div>
                    </div>
                </div>
                @if(($totalChapters ?? 0) > 0)
                <div class="ne-progress">
                    <div class="ne-progress-bar" style="width: {{ $progressPct }}%;"></div>
                    <span class="ne-progress-lbl">{{ $progressPct }}% опубликовано</span>
                </div>
                @endif
            </div>
            <div class="ne-hero-actions">
                <x-eriiba.btn :href="route('my-novels')" wire:navigate icon="arrow-left">Назад</x-eriiba.btn>
                @if(!$isEditor && $novelId)
                    <x-eriiba.btn :href="route('novel.show', $novel->id)" wire:navigate icon="eye">Посмотреть</x-eriiba.btn>
                    <x-eriiba.btn :href="route('author.novel.import', $novelId)" wire:navigate icon="file-import">Импорт</x-eriiba.btn>
                @endif
                @if(!$isEditor)
                    <x-eriiba.btn variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="cover_image, background_image, save">
                        <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i> Сохранить</span>
                        <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i> Сохранение…</span>
                    </x-eriiba.btn>
                @endif
            </div>
        </div>
        @else
        
        <div class="editor-head">
            <div>
                <h1>Новый проект</h1>
                <p style="color:var(--text-muted); margin:4px 0 0; font-size:13px;">Заполните основные поля и сохраните, после этого появятся вкладки «Содержание» и «Команда».</p>
            </div>
            <div class="spacer"></div>
            <x-eriiba.btn :href="route('my-novels')" wire:navigate icon="arrow-left">Назад</x-eriiba.btn>
            @if(!$isEditor)
                <x-eriiba.btn variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="cover_image, background_image, save">
                    <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i> Создать</span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i> Сохранение…</span>
                </x-eriiba.btn>
            @endif
        </div>
        @endif

        @if(session('success'))
            <div class="eri-alert ok" style="margin-bottom:18px;"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="eri-alert err" style="margin-bottom:18px;"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        @if(!$isEditor)
        
        @if($novelId)
        <div class="ne-tabs">
            <button type="button" @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'ne-tab on' : 'ne-tab'">
                <i class="fa-solid fa-sliders"></i> Настройки
            </button>
            <button type="button" @click="activeTab = 'chapters'" :class="activeTab === 'chapters' ? 'ne-tab on' : 'ne-tab'">
                <i class="fa-solid fa-list-ol"></i> Содержание
                <span class="ne-tab-count">{{ $totalChapters ?? 0 }}</span>
            </button>
            <button type="button" @click="activeTab = 'team'" :class="activeTab === 'team' ? 'ne-tab on' : 'ne-tab'">
                <i class="fa-solid fa-users"></i> Команда
            </button>
        </div>
        @endif

        <div x-show="activeTab === 'settings'" x-cloak>
            <form wire:submit.prevent="save" class="ne-settings-grid">

                <div class="ne-col-side">
                    <section class="editor-section">
                        <h2>Изображения</h2>

                        <div style="margin-bottom:18px;">
                            <label class="eri-label">Обложка <span class="ne-hint">(2:3, до 5 МБ)</span></label>
                            <div class="ne-upload ne-upload-cover">
                                @if($cover_image && $cover_image->isPreviewable())
                                    <img src="{{ $cover_image->temporaryUrl() }}" alt="">
                                @elseif($existingCover)
                                    <img src="{{ \App\Models\Novel::storageUrl($existingCover) }}?v={{ $novel?->updated_at?->timestamp ?? time() }}" alt="">
                                @else
                                    <div class="ne-upload-empty">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Загрузить обложку</span>
                                        <small>JPG, PNG, WebP</small>
                                    </div>
                                @endif
                                <div wire:loading wire:target="cover_image" class="ne-upload-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
                                <input type="file" wire:model="cover_image" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            @error('cover_image') <span class="ne-err">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="eri-label">Фон страницы <span class="ne-hint">(до 10 МБ)</span></label>
                            <div class="ne-upload ne-upload-bg">
                                @if($background_image && $background_image->isPreviewable())
                                    <img src="{{ $background_image->temporaryUrl() }}" alt="">
                                @elseif($existingBackground)
                                    <img src="{{ \App\Models\Novel::storageUrl($existingBackground) }}?v={{ $novel?->updated_at?->timestamp ?? time() }}" alt="">
                                @else
                                    <div class="ne-upload-empty">
                                        <i class="fa-solid fa-panorama"></i>
                                        <span>Загрузить фон</span>
                                    </div>
                                @endif
                                <div wire:loading wire:target="background_image" class="ne-upload-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>
                                <input type="file" wire:model="background_image" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            @error('background_image') <span class="ne-err">{{ $message }}</span> @enderror
                        </div>

                        @if(Auth::user()->hasRole('super_admin'))
                        <label class="ne-adult-toggle">
                            <span><i class="fa-solid fa-shield-halved"></i> 18+ контент</span>
                            <input type="checkbox" wire:model="is_adult">
                        </label>
                        @endif
                    </section>
                </div>

                <div class="ne-col-main">

                    <section class="editor-section" x-data="{ titleLen: @js(mb_strlen($title ?? '')) }">
                        <h2><i class="fa-solid fa-feather" style="color:var(--accent); margin-right:6px;"></i>Основное</h2>

                        <div class="ne-field">
                            <label class="eri-label" style="display:flex; justify-content:space-between;">
                                <span>Название <span style="color:var(--err);">*</span></span>
                                <span class="ne-counter" :class="titleLen > 100 ? 'over' : ''"><span x-text="titleLen"></span>/100</span>
                            </label>
                            <input type="text" wire:model="title" @input="titleLen = $event.target.value.length"
                                class="eri-input ne-title-input" placeholder="Название произведения" maxlength="120">
                            <span class="ne-hint-row"><i class="fa-solid fa-lightbulb"></i> Краткое и запоминающееся. Видно в каталоге и на главной.</span>
                            @error('title') <span class="ne-err">{{ $message }}</span> @enderror
                        </div>

                        <div class="ne-field">
                            <label class="eri-label">Описание <span style="color:var(--err);">*</span></label>
                            {{ $this->form }}
                            <span class="ne-hint-row"><i class="fa-solid fa-circle-info"></i> Первый абзац — самый важный, его покажут в подсказках поиска.</span>
                            @error('description') <span class="ne-err">{{ $message }}</span> @enderror
                        </div>

                        <div class="ne-field">
                            <label class="eri-label">Доп. информация</label>
                            <textarea wire:model="extra_info" rows="2" class="eri-textarea" placeholder="Перевод с английского, новые главы по понедельникам…"></textarea>
                        </div>
                    </section>

                    <section class="editor-section">
                        <h2><i class="fa-solid fa-sliders" style="color:var(--accent); margin-right:6px;"></i>Параметры</h2>
                        <div class="editor-grid cols-2">
                            <div class="ne-field">
                                <label class="eri-label">Статус произведения</label>
                                <select wire:model="status" class="eri-select">
                                    <option value="ongoing">Выходит</option>
                                    <option value="completed">Завершён</option>
                                    <option value="hiatus">Заморожен</option>
                                </select>
                                <span class="ne-hint-row">Влияет на отображение в каталоге.</span>
                            </div>
                            <div class="ne-field">
                                <label class="eri-label">Цена подписки на новеллу (₽)</label>
                                <input type="number" wire:model="price" min="0" class="eri-input" placeholder="0 = бесплатно">
                                <span class="ne-hint-row">Если 0 — главы продаются по отдельности.</span>
                            </div>
                            <div class="ne-field">
                                <label class="eri-label">Автор (отображается)</label>
                                <input type="text" wire:model="author_name" class="eri-input" placeholder="Ваш творческий псевдоним">
                                <span class="ne-hint-row">Если пусто — будет показан ваш ник.</span>
                            </div>
                            <div class="ne-field">
                                <label class="eri-label">Автор оригинала</label>
                                <input type="text" wire:model="original_author" class="eri-input" placeholder="Для переводов">
                                <span class="ne-hint-row">Заполните, если это перевод.</span>
                            </div>
                            <div class="ne-field">
                                <label class="eri-label">Год выпуска</label>
                                <input type="number" wire:model="release_year" min="1900" max="2100" class="eri-input" placeholder="{{ date('Y') }}">
                            </div>
                            <div class="ne-field">
                                <label class="eri-label">Ссылка на оригинал</label>
                                <input type="url" wire:model="original_source" class="eri-input" placeholder="https://…">
                                <span class="ne-hint-row">Линк на источник (правовая чистота).</span>
                            </div>
                        </div>
                    </section>

                    @if($novelId)
                    <section class="editor-section">
                        <h2><i class="fa-solid fa-coins" style="color:#f59e0b; margin-right:6px;"></i>Платный контент</h2>
                        <p style="color:var(--text-muted); font-size:13px; margin:0 0 14px;">Платные главы будут открываться автоматически по очереди.</p>
                        <div class="editor-grid cols-2">
                            <div class="ne-field">
                                <label class="eri-label">Интервал автооткрытия (дней)</label>
                                <input type="number" wire:model="auto_unlock_interval_days" min="1" max="365" placeholder="Выкл." class="eri-input">
                                <span class="ne-hint-row">Пусто = выключено.</span>
                            </div>
                            <div class="ne-field" style="display:flex; flex-direction:column; justify-content:flex-end;">
                                @if($novel && $novel->auto_unlock_last_at)
                                    <p style="font-size:12px; color:var(--text-muted); margin:0 0 6px;">
                                        Последнее открытие: <strong>{{ $novel->auto_unlock_last_at->format('d.m.Y H:i') }}</strong>
                                    </p>
                                @endif
                                @php $nextLockedCount = $novel ? \App\Models\Chapter::where('novel_id', $novel->id)->where('is_published', true)->where('is_locked', true)->count() : 0; @endphp
                                @if($nextLockedCount > 0)
                                    <p style="font-size:12px; color:#d97706; margin:0; font-weight:600;">Осталось платных глав: {{ $nextLockedCount }}</p>
                                @else
                                    <p style="font-size:12px; color:var(--ok); margin:0; font-weight:600;">Все главы уже бесплатные</p>
                                @endif
                            </div>
                        </div>
                    </section>
                    @endif

                    <section class="editor-section">
                        <h2><i class="fa-solid fa-tags" style="color:var(--accent); margin-right:6px;"></i>Жанры и теги</h2>
                        <p style="color:var(--text-muted); font-size:13px; margin:0 0 14px;">
                            Жанры — крупные категории, теги — конкретные мотивы.
                        </p>
                        <div class="editor-grid cols-2">
                            @if(isset($allGenres) && $allGenres->count() > 0)
                            <div x-data="{ search: '' }">
                                <label class="eri-label" style="display:flex; justify-content:space-between;">
                                    <span>Жанры</span>
                                    <span class="ne-counter">Выбрано: {{ count($genres ?? []) }}</span>
                                </label>
                                <input type="text" x-model="search" placeholder="Поиск жанра…" class="eri-input ne-chip-search">
                                <div class="ne-chip-pool">
                                    @foreach($allGenres as $genre)
                                        <button type="button" wire:click="toggleGenre({{ $genre->id }})"
                                                x-show="!search || '{{ mb_strtolower($genre->name) }}'.includes(search.toLowerCase())"
                                                class="ne-chip {{ in_array($genre->id, $genres ?? []) ? 'on' : '' }}">
                                            @if(in_array($genre->id, $genres ?? []))<i class="fa-solid fa-check"></i>@endif
                                            {{ $genre->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if(isset($allTags) && $allTags->count() > 0)
                            <div x-data="{ search: '' }">
                                <label class="eri-label" style="display:flex; justify-content:space-between;">
                                    <span>Теги</span>
                                    <span class="ne-counter">Выбрано: {{ count($tags ?? []) }}</span>
                                </label>
                                <input type="text" x-model="search" placeholder="Поиск тега…" class="eri-input ne-chip-search">
                                <div class="ne-chip-pool">
                                    @foreach($allTags as $tag)
                                        <button type="button" wire:click="toggleTag({{ $tag->id }})"
                                                x-show="!search || '{{ mb_strtolower($tag->name) }}'.includes(search.toLowerCase())"
                                                class="ne-chip {{ in_array($tag->id, $tags ?? []) ? 'on' : '' }}">
                                            @if(in_array($tag->id, $tags ?? []))<i class="fa-solid fa-check"></i>@endif
                                            {{ $tag->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </section>

                    @if($novelId)
                    <div class="ne-save-bar">
                        <span class="ne-save-bar-hint"><i class="fa-solid fa-circle-info"></i> Изменения вступят в силу после сохранения.</span>
                        <x-eriiba.btn variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="cover_image, background_image, save">
                            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-check"></i> Сохранить изменения</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i> Сохранение…</span>
                        </x-eriiba.btn>
                    </div>
                    @endif
                </div>
            </form>
            <x-filament-actions::modals />
        </div>

        @if($novel)
        <div x-show="activeTab === 'chapters'" x-cloak>
            <section class="editor-section">
                @livewire('author.chapter-manager', ['novel' => $novel, 'isEditor' => $isEditor, 'editorCanReadPaid' => $editorCanReadPaid], key($novel->id))
            </section>
        </div>

        <div x-show="activeTab === 'team'" x-cloak>

            @if(!$isEditor)
            <section class="editor-section" style="margin-bottom:20px;">
                <h2 style="display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-people-group" style="color:var(--accent);"></i>
                    Команда переводчиков
                    <span style="font-size:11px;font-weight:400;color:var(--text-muted);margin-left:4px;">Привяжите команду и настройте доли дохода</span>
                </h2>
                @livewire('author.novel-team-manager', ['novel' => $novel], key('team-mgr-'.$novel->id))
            </section>
            @endif

            <div class="editor-grid" style="grid-template-columns:repeat(3, 1fr);">
                <section class="editor-section">
                    <h2><i class="fa-solid fa-pen-nib" style="color:var(--accent); margin-right:6px;"></i>Редакторы</h2>
                    <p style="font-size:12px; color:var(--text-muted); margin:0 0 14px;">Работа над текстом. Можно дать доступ к платным главам.</p>
                    @livewire('author.editor-manager', ['novel' => $novel], key('editors-'.$novel->id))
                </section>
                <section class="editor-section">
                    <h2><i class="fa-solid fa-users" style="color:var(--accent); margin-right:6px;"></i>Бета-ридеры</h2>
                    <p style="font-size:12px; color:var(--text-muted); margin:0 0 14px;">Читают неопубликованные и платные главы бесплатно.</p>
                    @livewire('author.beta-reader-manager', ['novel' => $novel], key('beta-'.$novel->id))
                </section>
                <section class="editor-section">
                    <h2><i class="fa-solid fa-rocket" style="color:#f59e0b; margin-right:6px;"></i>Продвижение</h2>
                    <p style="font-size:12px; color:var(--text-muted); margin:0 0 14px;">Главная страница или топ каталога.</p>
                    @livewire('author.promote-novel', ['novel' => $novel], key('promo-'.$novel->id))
                </section>
            </div>
        </div>
        @endif

        @else
        
        @if($novel)
            <section class="editor-section">
                @livewire('author.chapter-manager', ['novel' => $novel, 'isEditor' => $isEditor, 'editorCanReadPaid' => $editorCanReadPaid], key($novel->id))
            </section>
        @endif
        @endif
    </div>

    <style>
        /* ── HERO CARD ── */
        .ne-hero {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            align-items: center;
            background: linear-gradient(135deg, var(--surface), var(--surface-2));
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 20px 24px;
            margin-bottom: 22px;
        }
        .ne-hero-cover { width: 100px; height: 140px; border-radius: 8px; overflow: hidden; background: var(--surface-2); flex-shrink: 0; }
        .ne-hero-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ne-hero-cover-empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 32px; }

        .ne-hero-body { min-width: 0; }
        .ne-hero-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 6px; }
        .ne-hero-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.4px;
            border-radius: 10px;
            background: var(--surface-2); color: var(--text-muted);
        }
        .ne-hero-badge.ok     { background: rgba(34,197,94,0.12); color: #16a34a; }
        .ne-hero-badge.accent { background: var(--accent-soft); color: var(--accent); }
        .ne-hero-badge.warn   { background: #fef3c7; color: #92400e; }

        .ne-hero-title { font-family: var(--display); font-size: clamp(22px, 3vw, 32px); font-weight: 600; letter-spacing: -0.02em; margin: 0 0 14px; color: var(--text); line-height: 1.15; }

        .ne-hero-stats { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 12px; }
        .ne-stat {}
        .ne-stat-num { font-family: var(--display); font-size: 22px; font-weight: 600; color: var(--text); line-height: 1; }
        .ne-stat-lbl { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px; }

        .ne-progress {
            position: relative; width: 100%; height: 6px;
            background: var(--surface-2); border-radius: 4px; overflow: hidden;
            margin-top: 4px;
        }
        .ne-progress-bar {
            position: absolute; left: 0; top: 0; bottom: 0;
            background: linear-gradient(90deg, var(--accent), #5b8def);
            border-radius: 4px; transition: width 0.3s;
        }
        .ne-progress-lbl {
            position: absolute; right: 0; top: -16px;
            font-size: 10px; color: var(--text-muted); font-weight: 600;
        }

        .ne-hero-actions { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0; }

        @media (max-width: 720px) {
            .ne-hero { grid-template-columns: 80px 1fr; }
            .ne-hero-actions { grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; align-items: stretch; }
            .ne-hero-cover { width: 80px; height: 112px; }
            .ne-hero-stats { gap: 16px; }
            .ne-stat-num { font-size: 18px; }
        }

        /* ── TABS ── */
        .ne-tabs {
            display: flex; gap: 4px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }
        .ne-tab {
            background: transparent; border: 0;
            padding: 10px 16px; font-size: 14px; font-weight: 500;
            color: var(--text-muted); cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            display: inline-flex; align-items: center; gap: 8px;
            transition: color 0.15s, border-color 0.15s;
        }
        .ne-tab:hover { color: var(--text); }
        .ne-tab.on { color: var(--accent); border-bottom-color: var(--accent); font-weight: 600; }
        .ne-tab-count {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 22px; padding: 0 6px; height: 18px;
            font-size: 10px; font-weight: 700;
            background: var(--surface-2); color: var(--text-muted);
            border-radius: 10px;
        }
        .ne-tab.on .ne-tab-count { background: var(--accent); color: var(--accent-text); }

        /* ── SETTINGS LAYOUT (двухколоночный: изображения слева, поля справа) ── */
        .ne-settings-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 18px;
            align-items: start;
        }
        .ne-col-side { position: sticky; top: 80px; }
        .ne-col-main { min-width: 0; }
        @media (max-width: 1024px) {
            .ne-settings-grid { grid-template-columns: 1fr; }
            .ne-col-side { position: static; }
        }

        /* ── UPLOAD ── */
        .ne-upload {
            position: relative;
            background: var(--surface-2);
            border: 2px dashed var(--border);
            border-radius: var(--r-sm);
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .ne-upload:hover { border-color: var(--accent); }
        .ne-upload-cover { aspect-ratio: 2/3; max-width: 220px; }
        .ne-upload-bg    { height: 110px; }
        .ne-upload img   { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ne-upload input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .ne-upload-empty {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-muted); padding: 14px; text-align: center;
        }
        .ne-upload-empty i { font-size: 24px; margin-bottom: 8px; }
        .ne-upload-empty span { font-size: 12px; font-weight: 600; }
        .ne-upload-empty small { font-size: 10px; margin-top: 2px; opacity: 0.7; }
        .ne-upload-loading {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0.55); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }

        /* ── ADULT TOGGLE ── */
        .ne-adult-toggle {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 16px; padding: 12px 14px;
            border: 1px solid var(--err); border-radius: var(--r-sm);
            background: color-mix(in srgb, var(--err) 8%, transparent);
            cursor: pointer;
        }
        .ne-adult-toggle span { color: var(--err); font-weight: 600; font-size: 13px; }

        /* ── FIELDS ── */
        .ne-field { margin-bottom: 16px; }
        .ne-field:last-child { margin-bottom: 0; }
        .ne-hint { color: var(--text-muted); font-weight: 400; font-size: 11px; }
        .ne-hint-row { font-size: 11px; color: var(--text-muted); display: block; margin-top: 5px; }
        .ne-hint-row i { color: var(--accent); margin-right: 4px; }
        .ne-counter { font-size: 10px; color: var(--text-muted); font-weight: 400; font-family: monospace; }
        .ne-counter.over { color: var(--err); font-weight: 600; }
        .ne-err { color: var(--err); font-size: 12px; margin-top: 4px; display: block; }
        .ne-title-input { font-size: 16px; font-weight: 600; }

        /* ── CHIP POOL (жанры/теги) ── */
        .ne-chip-search { padding: 6px 10px; font-size: 12px; margin-bottom: 8px; }
        .ne-chip-pool {
            display: flex; flex-wrap: wrap; gap: 6px;
            max-height: 240px; overflow-y: auto;
            padding: 6px;
            background: var(--surface-2);
            border-radius: var(--r-sm);
            border: 1px solid var(--border);
        }
        .ne-chip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            font-size: 12px; font-weight: 500;
            color: var(--text-3); cursor: pointer;
            transition: all 0.12s;
        }
        .ne-chip:hover { border-color: var(--accent); color: var(--accent); }
        .ne-chip.on { background: var(--accent); border-color: var(--accent); color: var(--accent-text); }
        .ne-chip i { font-size: 9px; }

        /* ── STICKY SAVE BAR ── */
        .ne-save-bar {
            position: sticky; bottom: 12px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 12px 16px;
            margin-top: 14px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.08);
            backdrop-filter: blur(8px);
            z-index: 5;
        }
        .ne-save-bar-hint { font-size: 12px; color: var(--text-muted); }
        .ne-save-bar-hint i { color: var(--accent); margin-right: 4px; }
    </style>
</div>
