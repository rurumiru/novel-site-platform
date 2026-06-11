<?php
namespace App\Filament\Resources\NovelResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Volume;

class ChaptersRelationManager extends RelationManager {
    protected static string $relationship = 'chapters';
    protected static ?string $title = 'Главы';

    public function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                Forms\Components\TextInput::make('title')->required()->label('Название'),
                Forms\Components\Select::make('volume_id')
                    ->relationship('volume', 'title', function ($query, RelationManager $livewire) {
                        return $query->where('novel_id', $livewire->getOwnerRecord()->id)->orderBy('sort_order');
                    })
                    ->label('Том')->placeholder('Без тома')->searchable()->preload(),
                Forms\Components\Select::make('_position')
                    ->label('Куда вставить')
                    ->options([
                        'end'   => 'В конец тома',
                        'start' => 'В начало тома',
                        'after' => 'После конкретной главы…',
                    ])
                    ->default('end')
                    ->required()
                    ->live()
                    ->visibleOn('create'),
                Forms\Components\Select::make('_after_chapter_id')
                    ->label('Вставить после главы')
                    ->options(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()
                            ->chapters()
                            ->orderBy('sort_order')
                            ->get(['id', 'title', 'sort_order'])
                            ->mapWithKeys(fn ($ch) => [$ch->id => '#' . $ch->sort_order . ' — ' . $ch->title])
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Том новой главы будет таким же, как у выбранной.')
                    ->visible(fn (Forms\Get $get) => $get('_position') === 'after')
                    ->visibleOn('create'),
                Forms\Components\DateTimePicker::make('published_at')->label('Дата публикации')->nullable(),
                Forms\Components\Toggle::make('is_locked')->label('🔒 Платная')->live(),
                Forms\Components\TextInput::make('price')->label('Цена')->numeric()->default(10)->hidden(fn (Forms\Get $get) => !$get('is_locked'))->required(fn (Forms\Get $get) => $get('is_locked')),
                Forms\Components\Toggle::make('is_published')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Содержание')->schema([
                Forms\Components\MarkdownEditor::make('content')
                    ->label('Текст главы')
                    ->fileAttachmentsDisk('s3')
                    ->fileAttachmentsDirectory('chapters_media')
                    ->fileAttachmentsVisibility('public')
                    ->required(),
            ]),
        ]);
    }

    public function table(Table $table): Table {
        return $table->recordTitleAttribute('title')->columns([
            Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable()->width(50),
            Tables\Columns\TextColumn::make('title')->searchable()->limit(50)->weight('bold'),
            Tables\Columns\TextColumn::make('volume.title')
                ->label('Том')
                ->badge()
                ->color('info')
                ->sortable()
                ->placeholder('Без тома'),
            Tables\Columns\TextColumn::make('published_at')->dateTime('d.m H:i')->label('Публикация')->toggleable(),
            Tables\Columns\TextColumn::make('price')
                ->label('Цена')
                ->formatStateUsing(fn ($state, $record) => $record->is_locked ? $state . ' ₽' : 'Free')
                ->badge()
                ->color(fn ($record) => $record->is_locked ? 'warning' : 'success'),
            Tables\Columns\IconColumn::make('is_published')->boolean()->label('Опубл.'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('volume_id')
                ->label('Том')
                ->options(function () {
                    $novel = $this->getOwnerRecord();
                    return $novel->volumes()->orderBy('sort_order')->pluck('title', 'id')->prepend('Без тома', '')->toArray();
                })
                ->query(function ($query, array $data) {
                    if ($data['value'] === '') return $query->whereNull('volume_id');
                    if ($data['value'])             return $query->where('volume_id', $data['value']);
                    return $query;
                }),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->using(function (array $data) {
                    $novel = $this->getOwnerRecord();
                    $volumeId = $data['volume_id'] ?? null;
                    $position = $data['_position'] ?? 'end';
                    $afterChapterId = !empty($data['_after_chapter_id']) ? (int) $data['_after_chapter_id'] : null;
                    unset($data['_position'], $data['_after_chapter_id']);

                    if ($position === 'after' && $afterChapterId) {
                        $afterCh = \App\Models\Chapter::where('novel_id', $novel->id)->find($afterChapterId);
                        if ($afterCh) {
                            $volumeId = $afterCh->volume_id;
                            $sortOrder = $afterCh->sort_order + 1;
                        } else {
                            $sortOrder = $novel->getNextSortOrderForVolume($volumeId);
                        }
                    } elseif ($position === 'start') {
                        $sortOrder = $novel->getFirstSortOrderForVolume($volumeId);
                    } else {
                        $sortOrder = $novel->getNextSortOrderForVolume($volumeId);
                    }

                    $novel->shiftChaptersFrom($sortOrder);
                    $data['sort_order'] = $sortOrder;
                    $data['volume_id']  = $volumeId;
                    $chapter = $novel->chapters()->create($data);
                    $novel->recalculateChapterSortOrders();
                    return $chapter;
                }),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('make_free')
                    ->label('Сделать бесплатными')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Выбранные главы станут бесплатными (цена = 0).')
                    ->action(fn (Collection $records) => $records->each->update(['is_locked' => false, 'price' => 0]))
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('make_paid')
                    ->label('Сделать платными')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('price')
                            ->label('Цена (₽)')
                            ->numeric()
                            ->required()
                            ->default(10)
                            ->minValue(1)
                            ->helperText('Минимум 1 ₽'),
                    ])
                    ->action(fn (Collection $records, array $data) => $records->each->update(['is_locked' => true, 'price' => (int) $data['price']]))
                    ->deselectRecordsAfterCompletion(),
            ]),
        ])
        ->reorderable('sort_order')
        ->defaultSort('sort_order');
    }
}
