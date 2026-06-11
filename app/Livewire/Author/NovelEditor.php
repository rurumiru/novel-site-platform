<?php
namespace App\Livewire\Author;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Novel;
use App\Models\Tag;
use App\Services\ContentRenderer;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NovelEditor extends Component implements HasForms {
    use WithFileUploads, InteractsWithForms;

    public $novelId;
    public $title, $description, $status = 'ongoing', $price = 0, $tags = [], $genres = [];
    public $cover_image, $background_image;
    public $existingCover, $existingBackground;
    public $author_name, $original_author, $original_source, $release_year, $extra_info;
    public $is_adult = false;
    public $auto_unlock_interval_days;
    public bool $isEditor = false;
    public bool $editorCanReadPaid = false;

    public function mount($id = null) {
        if ($id) {
            $novel = Novel::findOrFail($id);
            $isOwner = $novel->user_id === Auth::id();
            $isAdmin = Auth::user()->hasRole(['super_admin', 'moderator']);
            $editorPivot = $novel->editors()->where('users.id', Auth::id())->first();
            $this->isEditor = !$isOwner && !$isAdmin && !!$editorPivot;
            $this->editorCanReadPaid = $editorPivot?->pivot?->can_read_paid ?? false;
            if (!$isOwner && !$isAdmin && !$this->isEditor) {
                abort(403);
            }
            $this->novelId = $novel->id;
            $this->title = $novel->title;
            $this->description = ContentRenderer::htmlToMarkdown($novel->description);
            $this->status = $novel->status;
            $this->price = $novel->price;
            $this->tags = $novel->tags->pluck('id')->toArray();
            $this->existingCover = $novel->cover_image;
            $this->existingBackground = $novel->background_image;
            $this->author_name = $novel->author_name;
            $this->original_author = $novel->original_author;
            $this->original_source = $novel->original_source;
            $this->release_year = $novel->release_year;
            $this->extra_info = $novel->extra_info;
            $this->genres = $novel->genres->pluck('id')->toArray();
            $this->is_adult = $novel->is_adult;
            $this->auto_unlock_interval_days = $novel->auto_unlock_interval_days;
        }
        $this->form->fill(['description' => $this->description ?? '']);
    }

    public function updatedCoverImage() {
        $this->validateUploadSafely('cover_image', 5 * 1024 * 1024, 'Обложка');
    }

    public function updatedBackgroundImage() {
        $this->validateUploadSafely('background_image', 10 * 1024 * 1024, 'Баннер');
    }

    private function validateUploadSafely(string $prop, int $maxBytes, string $label): void {
        $file = $this->{$prop};
        if (!$file) return;

        try {
            $ext  = strtolower((string) $file->getClientOriginalExtension());
            $size = (int) $file->getSize();
        } catch (\Throwable $e) {
            try {
                $readProtected = static function ($obj, string $name) {
                    try {
                        $r = new \ReflectionObject($obj);
                        if ($r->hasProperty($name)) {
                            $p = $r->getProperty($name);
                            $p->setAccessible(true);
                            return $p->getValue($obj);
                        }
                    } catch (\Throwable $ignore) {}
                    return null;
                };
                \Log::error('NovelEditor upload broken: ' . $e->getMessage(), [
                    'prop'           => $prop,
                    'exception'      => get_class($e),
                    'class'          => is_object($file) ? get_class($file) : gettype($file),
                    'orig_name'      => is_object($file) && method_exists($file, 'getClientOriginalName') ? @$file->getClientOriginalName() : null,
                    'real_path'      => is_object($file) && method_exists($file, 'getRealPath')         ? @$file->getRealPath()         : null,
                    'reflect_path'   => is_object($file) ? $readProtected($file, 'path')    : null,
                    'reflect_disk'   => is_object($file) ? $readProtected($file, 'disk')    : null,
                    'temp_disk'      => config('livewire.temporary_file_upload.disk'),
                    'temp_dir'       => config('livewire.temporary_file_upload.directory'),
                    'default_disk'   => config('filesystems.default'),
                ]);
            } catch (\Throwable $logFail) {
                \Log::error('NovelEditor upload broken (and logging failed): ' . $e->getMessage() . ' | log error: ' . $logFail->getMessage());
            }
            $this->{$prop} = null;
            $this->addError($prop, $label . ': не удалось обработать файл, попробуйте ещё раз.');
            return;
        }

        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
            $this->{$prop} = null;
            $this->addError($prop, $label . ': допустимы только JPG, PNG, GIF, WebP.');
            return;
        }
        if ($size > $maxBytes) {
            $this->{$prop} = null;
            $this->addError($prop, $label . ': максимум ' . (int) round($maxBytes / 1024 / 1024) . ' МБ.');
            return;
        }
    }

    public function form(Form $form): Form {
        return $form->schema([
            MarkdownEditor::make('description')
                ->label('')
                ->placeholder('Краткое описание новеллы. Поддерживается Markdown.')
                ->fileAttachmentsDisk('s3')
                ->fileAttachmentsDirectory('novels_media')
                ->fileAttachmentsVisibility('public')
                ->required(),
        ]);
    }

    public function save() {
        $this->validate([
            'title' => 'required|min:3',
            'description' => 'required|min:10',
            'cover_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'background_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ], [
            'cover_image.mimes' => 'Обложка: допустимы только JPG, PNG, GIF, WebP.',
            'cover_image.max' => 'Обложка: максимум 5 МБ.',
            'background_image.mimes' => 'Баннер: допустимы только JPG, PNG, GIF, WebP.',
            'background_image.max' => 'Баннер: максимум 10 МБ.',
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'price' => $this->price,
            'author_name' => $this->author_name,
            'original_author' => $this->original_author,
            'original_source' => $this->original_source,
            'release_year' => $this->release_year,
            'extra_info' => $this->extra_info,
            'user_id' => Auth::id(),
            'auto_unlock_interval_days' => $this->auto_unlock_interval_days ?: null,
        ];

        if (Auth::user()->hasRole('super_admin')) {
            $data['is_adult'] = $this->is_adult;
        }

        try {
            if ($this->cover_image) {
                $data['cover_image'] = $this->cover_image->store('covers', 's3');
            }
            if ($this->background_image) {
                $data['background_image'] = $this->background_image->store('backgrounds', 's3');
            }
        } catch (\Throwable $e) {
            \Log::error('NovelEditor save images: ' . $e->getMessage());
            session()->flash('error', 'Ошибка сохранения изображений.');
            return;
        }

        $needsModeration = !Auth::user()->can_create_novels && !Auth::user()->hasRole('super_admin');
        if ($this->novelId) {
            $novel = Novel::find($this->novelId);
            if ($this->cover_image) $data['cover_moderation_status'] = $needsModeration ? 'pending' : 'approved';
            if ($needsModeration && trim((string)$novel->description) !== trim((string)$this->description)) {
                $data['description_moderation_status'] = 'pending';
            }
            $novel->update($data);
        } else {
            if ($needsModeration) {
                $data['moderation_status'] = 'pending';
                $data['is_published'] = false;
                $data['cover_moderation_status'] = 'pending';
                $data['description_moderation_status'] = 'pending';
            }
            $novel = Novel::create($data);
            $this->novelId = $novel->id;
        }

        $tagIds = array_values(array_filter(array_map('intval', (array) $this->tags), fn($id) => $id > 0));
        $novel->tags()->sync($tagIds);

        $genreIds = array_values(array_filter(array_map('intval', (array) ($this->genres ?? [])), fn($id) => $id > 0));
        $novel->genres()->sync($genreIds);

        if ($this->cover_image) {
            $this->existingCover = $novel->fresh()->cover_image;
            $this->cover_image = null;
        }
        if ($this->background_image) {
            $this->existingBackground = $novel->fresh()->background_image;
            $this->background_image = null;
        }

        session()->flash('success', $needsModeration && !$this->novelId 
            ? 'Новелла создана и отправлена на модерацию. Она появится на сайте после одобрения.' 
            : 'Сохранено!');
    }

    public function toggleGenre($id) {
        $arr = $this->genres ?? [];
        if (in_array($id, $arr)) {
            $this->genres = array_values(array_diff($arr, [$id]));
        } else {
            $this->genres = array_merge($arr, [$id]);
        }
    }

    public function toggleTag($id) {
        $arr = $this->tags ?? [];
        if (in_array($id, $arr)) {
            $this->tags = array_values(array_diff($arr, [$id]));
        } else {
            $this->tags = array_merge($arr, [$id]);
        }
    }

    public function render() {
        return view('livewire.author.novel-editor', [
            'allTags' => Tag::all(),
            'allGenres' => \App\Models\Genre::orderBy('name')->get(),
            'novel' => $this->novelId ? Novel::find($this->novelId) : null
        ])
        ->layout('layouts.app')
        ->title($this->novelId ? 'Редактирование' : 'Создание');
    }
}
