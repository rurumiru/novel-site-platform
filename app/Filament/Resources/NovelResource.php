<?php
namespace App\Filament\Resources;
use App\Models\Chapter;
use App\Models\Novel;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;

class NovelResource extends Resource {
    protected static ?string $model = Novel::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Новеллы';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Новелла';
    protected static ?string $pluralModelLabel = 'Новеллы';

    public static function getNavigationBadge(): ?string {
        $count = Novel::where('moderation_status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): ?string { return 'warning'; }

    public static function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\Section::make('Основное')->schema([
                Forms\Components\TextInput::make('title')->required()->label('Название')->columnSpanFull(),
                Forms\Components\TextInput::make('author_name')->label('Автор (текст)'),
                Forms\Components\TextInput::make('original_author')->label('Автор оригинала'),
                Forms\Components\TextInput::make('original_source')->label('Ссылка на оригинал')->url(),
                Forms\Components\TextInput::make('release_year')->label('Год выпуска')->numeric(),
                Forms\Components\Select::make('status')->options(['ongoing'=>'Выходит','completed'=>'Завершён','hiatus'=>'Заморожен'])->required()->label('Статус'),
                Forms\Components\DateTimePicker::make('next_chapter_at')
                    ->label('Таймер новой главы')
                    ->helperText('Условный таймер — отображается на странице новеллы. Открытие глав вручную.')
                    ->nullable(),
                Forms\Components\Select::make('user_id')->relationship('publisher', 'name')->searchable()->preload()->label('Издатель'),
                Forms\Components\Select::make('genres')->relationship('genres', 'name')->multiple()->preload()->label('Жанры'),
                Forms\Components\Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->label('Теги'),
            ])->columns(2),
            Forms\Components\Section::make('Переключатели')->schema([
                Forms\Components\Toggle::make('is_published')->label('Опубликовано'),
                Forms\Components\Toggle::make('is_restricted')->label('Блок РФ'),
                Forms\Components\Toggle::make('is_adult')->label('18+'),
                Forms\Components\Toggle::make('hide_from_guests')->label('Скрыть от гостей')
                    ->helperText('Новелла полностью невидима для незарегистрированных'),
            ])->columns(4),
            Forms\Components\Section::make('Медиа')->schema([
                Forms\Components\FileUpload::make('cover_image')->image()->directory('covers')->disk('s3')->visibility('public')->label('Обложка'),
                Forms\Components\FileUpload::make('background_image')->image()->directory('backgrounds')->disk('s3')->visibility('public')->label('Фон'),
            ])->columns(2)->collapsible(),
            Forms\Components\Section::make('Описание')->schema([
                Forms\Components\MarkdownEditor::make('description')
                    ->fileAttachmentsDisk('s3')->fileAttachmentsDirectory('novels_media')->fileAttachmentsVisibility('public')
                    ->label('Описание')->columnSpanFull(),
                Forms\Components\Textarea::make('extra_info')->label('Доп. информация')->rows(3)->columnSpanFull(),
            ])->collapsible(),
            Forms\Components\Section::make('Монетизация')->schema([
                Forms\Components\TextInput::make('price')->numeric()->label('Цена подписки (₽)')->default(0),
                Forms\Components\MarkdownEditor::make('subscription_info')
                    ->fileAttachmentsDisk('s3')->fileAttachmentsDirectory('novels_media')->fileAttachmentsVisibility('public')
                    ->label('Описание подписки'),
            ])->collapsible()->collapsed(),
            Forms\Components\Section::make('Авто-открытие платных глав')
                ->description('Каждые N дней открывается следующая платная глава по порядку.')
                ->schema([
                    Forms\Components\TextInput::make('auto_unlock_interval_days')
                        ->label('Интервал, дней')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Например: 7 — каждую неделю одна глава')
                        ->helperText('Оставьте пустым, чтобы отключить.'),
                    Forms\Components\DateTimePicker::make('auto_unlock_last_at')
                        ->label('Последнее авто-открытие')
                        ->helperText('Следующее произойдёт через указанный интервал. Очистите чтобы открыть сразу.')
                        ->nullable(),
                ])->collapsible()->collapsed()->columns(2),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('cover_image')->disk('s3')->width(40)->height(56)->label(''),
            Tables\Columns\TextColumn::make('title')->searchable()->weight('bold')->limit(40)->label('Название'),
            Tables\Columns\TextColumn::make('publisher.name')->label('Издатель')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn($state) => match($state) {
                'ongoing' => 'Выходит', 'completed' => 'Завершён', 'hiatus' => 'Заморожен', default => $state
            })->color(fn($state) => match($state) {
                'ongoing' => 'success', 'completed' => 'info', 'hiatus' => 'warning', default => 'gray'
            })->label('Статус'),
            Tables\Columns\TextColumn::make('chapters_count')->counts('chapters')->label('Глав')->sortable(),
            Tables\Columns\TextColumn::make('views')->label('Просм.')->sortable()->numeric(),
            Tables\Columns\ToggleColumn::make('is_published')->label('Опубл.'),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y')->label('Создана')->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['ongoing'=>'Выходит','completed'=>'Завершён','hiatus'=>'Заморожен'])->label('Статус'),
            Tables\Filters\TernaryFilter::make('is_published')->label('Опубликовано'),
            Tables\Filters\TernaryFilter::make('is_adult')->label('18+'),
            Tables\Filters\TernaryFilter::make('hide_from_guests')->label('Скрыто от гостей'),
        ])
        ->actions([
            Tables\Actions\Action::make('stats')->icon('heroicon-o-chart-bar')->url(fn($record) => route('author.novel.stats', $record->id))->label('Статистика'),
            Tables\Actions\Action::make('unlock_now')
                ->label('Открыть следующую главу')
                ->icon('heroicon-o-lock-open')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Открыть следующую заблокированную главу прямо сейчас (без ожидания cron).')
                ->action(function (Novel $record): void {
                    $next = Chapter::where('novel_id', $record->id)
                        ->where('is_published', true)
                        ->where('is_locked', true)
                        ->orderBy('sort_order', 'asc')
                        ->first();
                    if (!$next) {
                        Notification::make()->title('Заблокированных глав нет')->warning()->send();
                        return;
                    }
                    $next->update(['is_locked' => false, 'price' => 0]);
                    $record->update(['auto_unlock_last_at' => now()]);
                    Notification::make()
                        ->title('Глава открыта')
                        ->body("«{$next->title}» теперь доступна бесплатно")
                        ->success()
                        ->send();
                }),
            Tables\Actions\EditAction::make(),
        ])
        ->headerActions([
            Tables\Actions\Action::make('run_auto_unlock')
                ->label('Запустить авто-открытие сейчас')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Запускает cron-команду app:auto-unlock-chapters вручную, без ожидания расписания. Откроет по одной следующей платной главе во всех новеллах, где истёк интервал.')
                ->action(function (): void {
                    \Artisan::call('app:auto-unlock-chapters');
                    $output = trim(\Artisan::output());
                    Notification::make()
                        ->title('Авто-открытие выполнено')
                        ->body($output ?: 'Готово.')
                        ->success()
                        ->send();
                }),
        ])
        ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array {
        return [
            NovelResource\RelationManagers\VolumesRelationManager::class,
            NovelResource\RelationManagers\ChaptersRelationManager::class,
            NovelResource\RelationManagers\EditorsRelationManager::class,
        ];
    }

    public static function getPages(): array {
        return [
            'index' => NovelResource\Pages\ListNovels::route('/'),
            'create' => NovelResource\Pages\CreateNovel::route('/create'),
            'edit' => NovelResource\Pages\EditNovel::route('/{record}/edit'),
        ];
    }
}
