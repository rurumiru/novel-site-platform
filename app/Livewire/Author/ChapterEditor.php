<?php
namespace App\Livewire\Author;
use Livewire\Component;
use Livewire\WithFileUploads;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Models\BetaReader;
use App\Notifications\NewBetaChapter;
use App\Services\ContentRenderer;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChapterEditor extends Component implements HasForms {
    use WithFileUploads, InteractsWithForms;

    public $novel;
    public $chapter;
    public $chapterId;
    public array $formData = [];
    public ?string $content = '<p></p>';
    public $image = null;

    public ?int $prevChapterId = null;
    public ?int $nextChapterId = null;
    public ?string $prevChapterTitle = null;
    public ?string $nextChapterTitle = null;

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Основное')->schema([
                Forms\Components\TextInput::make('title')->required()->label('Название главы'),
                Forms\Components\TextInput::make('sort_order')->numeric()->required()->label('Номер')
                    ->visible(fn () => (bool) $this->chapterId),
                Forms\Components\Select::make('volume_id')
                    ->label('Том')
                    ->options(fn () => $this->novel->volumes()->orderBy('sort_order')->pluck('title', 'id'))
                    ->placeholder('Без тома'),
                Forms\Components\Select::make('chapter_position')
                    ->label('Куда вставить')
                    ->options([
                        'end'   => 'В конец тома',
                        'start' => 'В начало тома',
                        'after' => 'После конкретной главы…',
                    ])
                    ->default('end')
                    ->required()
                    ->live()
                    ->visible(fn () => !$this->chapterId),
                Forms\Components\Select::make('after_chapter_id')
                    ->label('Вставить после главы')
                    ->options(fn () => $this->novel->chapters()
                        ->orderBy('sort_order')
                        ->pluck('title', 'id')
                        ->mapWithKeys(fn ($title, $id) => [$id => '#' . ($this->novel->chapters()->find($id)?->sort_order ?? '?') . ' — ' . $title])
                        ->toArray())
                    ->searchable()
                    ->required()
                    ->helperText('Том новой главы будет таким же, как у выбранной.')
                    ->visible(fn (Forms\Get $get) => !$this->chapterId && $get('chapter_position') === 'after'),
                Forms\Components\Toggle::make('is_locked')->label('Платная глава')->live(),
                Forms\Components\TextInput::make('price')->numeric()->label('Цена (₽)')->default(0)->visible(fn (Forms\Get $get) => $get('is_locked')),
                Forms\Components\Toggle::make('is_published')->label('Опубликовано')->default(true),
                Forms\Components\Select::make('highlight')
                    ->label('Особая глава (цветная метка в списке)')
                    ->options([
                        'important'      => '🔥 Важная',
                        'arc-end'        => '🏁 Конец арки',
                        'arc-start'      => '🚀 Начало арки',
                        'breakthrough'   => '⚡ Прорыв',
                        'special'        => '✨ Спецглава',
                        'side-story'     => '📖 Побочная история',
                        'announcement'   => '📢 Объявление',
                    ])
                    ->placeholder('— обычная глава —')
                    ->helperText('Выделит главу цветом в общем списке.'),
            ])->columns(2),
        ])->statePath('formData');
    }

    public function mount($novel, $chapter = null) {
        $novelModel = Novel::findOrFail($novel);
        $user = Auth::user();
        $isOwner = $novelModel->user_id === $user->id;
        $isAdmin = $user->hasRole(['super_admin', 'moderator', 'deputy_admin', 'owner']);
        $editorPivot = $novelModel->editors()->where('users.id', $user->id)->first();
        $isEditor = !$isOwner && !$isAdmin && !!$editorPivot;
        if (!$isOwner && !$isAdmin && !$isEditor) {
            abort(403);
        }
        $this->novel = $novelModel;

        if ($isEditor) {
            $pivot = $editorPivot->pivot;
            if (!$pivot->can_edit_chapters) {
                abort(403, 'У вас нет права редактировать главы этой новеллы.');
            }
            if ($chapter) {
                $ch = Chapter::where('id', $chapter)->where('novel_id', $novelModel->id)->first();
                if ($ch && $ch->is_locked && !$pivot->can_read_paid) {
                    abort(403, 'Нет доступа к платным главам.');
                }
            }
        }

        if ($chapter) {
            $this->chapter = Chapter::where('id', $chapter)->where('novel_id', $this->novel->id)->firstOrFail();
            $this->chapterId = $this->chapter->id;
            $this->content = ContentRenderer::toHtml($this->chapter->content ?? '') ?: '<p></p>';
            $this->form->fill([
                'title' => $this->chapter->title,
                'price' => $this->chapter->price,
                'is_locked' => $this->chapter->is_locked,
                'is_published' => $this->chapter->is_published,
                'volume_id' => $this->chapter->volume_id,
                'sort_order' => $this->chapter->sort_order,
                'highlight' => $this->chapter->highlight,
            ]);
            $this->loadSiblings();
        } else {
            $atParam = request()->query('at') === 'start' ? 'start' : 'end';
            $volumeFromUrl = request()->query('volume');
            $volumeId = null;
            if ($volumeFromUrl !== null && $volumeFromUrl !== '' && $volumeFromUrl !== '0') {
                $exists = $this->novel->volumes()->where('id', (int) $volumeFromUrl)->exists();
                $volumeId = $exists ? (int) $volumeFromUrl : null;
            } elseif ($volumeFromUrl === '0') {
                $volumeId = null;
            } else {
                $volumeId = $this->novel->volumes()->orderBy('sort_order', 'desc')->first()?->id;
            }
            $this->content = '<p></p>';
            $this->form->fill([
                'volume_id' => $volumeId,
                'chapter_position' => $atParam,
                'is_published' => true,
            ]);
        }
    }

    private function loadSiblings(): void {
        if (!$this->chapter) return;

        $isOwner = $this->novel->user_id === Auth::id();
        $isAdmin = Auth::user()->hasRole(['super_admin', 'moderator']);
        $editorPivot = $this->novel->editors()->where('users.id', Auth::id())->first();
        $hideLocked = !$isOwner && !$isAdmin && $editorPivot && !($editorPivot->pivot?->can_read_paid);

        $base = Chapter::where('novel_id', $this->novel->id);
        if ($hideLocked) $base->where('is_locked', false);

        $prev = (clone $base)->where('sort_order', '<', $this->chapter->sort_order)
            ->orderBy('sort_order', 'desc')->orderBy('id', 'desc')->first(['id', 'title']);
        $next = (clone $base)->where('sort_order', '>', $this->chapter->sort_order)
            ->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->first(['id', 'title']);

        $this->prevChapterId    = $prev?->id;
        $this->prevChapterTitle = $prev?->title;
        $this->nextChapterId    = $next?->id;
        $this->nextChapterTitle = $next?->title;
    }

    public function updatedImage() {
        $this->validate(['image' => 'image|max:10240']);

        $path = $this->image->store('chapters_media', 's3');
        Storage::disk('s3')->setVisibility($path, 'public');

        $path = ImageOptimizer::optimize($path);
        Storage::disk('s3')->setVisibility($path, 'public');

        $url = Storage::disk('s3')->url($path);

        $this->dispatch('chapter-image-insert', url: $url);
        $this->image = null;
    }

    public function save() {
        $data = $this->form->getState();

        $position = $data['chapter_position'] ?? 'end';
        $afterChapterId = !empty($data['after_chapter_id']) ? (int) $data['after_chapter_id'] : null;
        unset($data['chapter_position'], $data['after_chapter_id']);

        $contentHtml = ContentRenderer::normalizeEditorHtml($this->content ?? '');
        if ($contentHtml === '' || $contentHtml === '<p></p>') {
            $this->addError('content', 'Текст главы не может быть пустым.');
            return;
        }
        $data['content'] = $contentHtml;
        $data['novel_id'] = $this->novel->id;
        $data['price'] = ($data['is_locked'] ?? false) ? ($data['price'] ?? 0) : 0;
        $data['volume_id'] = $data['volume_id'] ?: null;
        $data['highlight'] = !empty($data['highlight']) ? $data['highlight'] : null;

        $user = Auth::user();
        $isOwner = $this->novel->user_id === $user->id;
        $isAdmin = $user->hasRole(['super_admin', 'moderator', 'deputy_admin', 'owner']);
        $editorPivot = $isOwner || $isAdmin ? null : $this->novel->editors()->where('users.id', $user->id)->first();
        if ($editorPivot) {
            $pivot = $editorPivot->pivot;
            $original = $this->chapterId ? $this->chapter : null;

            if (!$pivot->can_publish_chapters) {
                $data['is_published'] = $original ? (bool) $original->is_published : false;
            }
            if (!$pivot->can_set_paid) {
                $data['is_locked'] = $original ? (bool) $original->is_locked : false;
                $data['price']     = $original ? (int) $original->price       : 0;
            }
        }

        if ($this->chapterId) {
            ChapterVersion::saveVersion($this->chapterId, Auth::id(), $data['content']);
            $this->chapter->update($data);
            session()->flash('success', 'Глава обновлена!');
            $this->chapter->refresh();
            $this->loadSiblings();
        } else {
            $volumeId = $data['volume_id'] ? (int) $data['volume_id'] : null;

            if ($position === 'after' && $afterChapterId) {
                $afterCh = Chapter::where('novel_id', $this->novel->id)->find($afterChapterId);
                if ($afterCh) {
                    $volumeId  = $afterCh->volume_id;
                    $sortOrder = $afterCh->sort_order + 1;
                } else {
                    $sortOrder = $this->novel->getNextSortOrderForVolume($volumeId);
                }
            } elseif ($position === 'start') {
                $sortOrder = $this->novel->getFirstSortOrderForVolume($volumeId);
            } else {
                $sortOrder = $this->novel->getNextSortOrderForVolume($volumeId);
            }

            $this->novel->shiftChaptersFrom($sortOrder);
            $data['sort_order'] = $sortOrder;
            $data['volume_id'] = $volumeId;
            $chapter = Chapter::create($data);

            $this->novel->recalculateChapterSortOrders();

            BetaReader::where('novel_id', $this->novel->id)
                ->with('user')
                ->get()
                ->each(fn($br) => optional($br->user)->notify(new NewBetaChapter($chapter)));

            session()->flash('success', 'Глава создана!');
            return $this->redirect(route('author.chapter.edit', [$this->novel->id, $chapter->id]), navigate: true);
        }
    }

    public function render() {
        return view('livewire.author.chapter-editor', [
            'volumes' => $this->novel->volumes
        ])
        ->layout('layouts.app')
        ->title($this->chapterId ? 'Редактирование главы' : 'Новая глава');
    }
}
