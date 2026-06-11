<?php
namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TagResource extends Resource {
    protected static ?string $model = Tag::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Теги';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int    $navigationSort  = 20;
    protected static ?string $modelLabel = 'Тег';
    protected static ?string $pluralModelLabel = 'Теги';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Основное')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (empty($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->helperText('URL-сегмент, латиницей. Оставьте пустым — заполнится автоматически.')
                        ->maxLength(140)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description')
                        ->label('Описание')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Группировка и внешний вид')
                ->description('Категория помогает сгруппировать теги в каталоге. Цвет и иконка отображаются на бейдже тега.')
                ->schema([
                    Forms\Components\TextInput::make('category')
                        ->label('Категория / группа')
                        ->datalist(fn () => Tag::query()
                            ->whereNotNull('category')
                            ->distinct()
                            ->pluck('category')
                            ->filter()
                            ->values()
                            ->all())
                        ->maxLength(60)
                        ->helperText('Например: «Жанры», «Тропы», «Сеттинг». Можно выбрать существующую или ввести новую.'),
                    Forms\Components\ColorPicker::make('color')
                        ->label('Цвет бейджа')
                        ->helperText('HEX, например #2f6df0. Пусто = цвет по умолчанию.'),
                    Forms\Components\TextInput::make('icon')
                        ->label('Иконка Font Awesome')
                        ->placeholder('fa-solid fa-fire')
                        ->helperText('Полный класс FA, например fa-solid fa-fire. Пусто = без иконки.')
                        ->maxLength(80),
                ])->columns(3),

            Forms\Components\Section::make('Ограничения')
                ->description('Эти флаги передаются на новеллу при сохранении тега: если у тега is_adult/is_restricted = да, все привязанные новеллы получают тот же флаг.')
                ->schema([
                    Forms\Components\Toggle::make('is_adult')
                        ->label('18+ (тег для взрослых)')
                        ->helperText('Новеллы с этим тегом будут помечены как 18+ и скрыты от гостей при соответствующей настройке.')
                        ->inline(false)
                        ->default(false),
                    Forms\Components\Toggle::make('is_restricted')
                        ->label('Запрет в РФ')
                        ->helperText('Новеллы с этим тегом будут блокироваться геофильтром для пользователей из РФ.')
                        ->inline(false)
                        ->default(false),
                    Forms\Components\Toggle::make('is_visible')
                        ->label('Виден на сайте')
                        ->helperText('Если выключено — тег скрыт из каталога и со страницы новеллы.')
                        ->inline(false)
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Порядок сортировки')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function ($state, Tag $record) {
                        $iconHtml = $record->icon
                            ? '<i class="' . e($record->icon) . '" style="margin-right:6px;opacity:.85;"></i>'
                            : '';
                        $color = $record->color ?: '#6b7280';
                        return new \Illuminate\Support\HtmlString(
                            '<span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;background:' . e($color) . '22;color:' . e($color) . ';font-weight:600;">'
                            . $iconHtml . e($state) . '</span>'
                        );
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('novels_count')
                    ->label('Новелл')
                    ->counts('novels')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_adult')
                    ->label('18+')
                    ->boolean()
                    ->trueColor('danger')
                    ->trueIcon('heroicon-o-no-symbol'),
                Tables\Columns\IconColumn::make('is_restricted')
                    ->label('РФ-блок')
                    ->boolean()
                    ->trueColor('warning')
                    ->trueIcon('heroicon-o-globe-alt'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Видим')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Обновлён')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->options(fn () => Tag::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category', 'category')
                        ->filter()
                        ->all()),
                Tables\Filters\TernaryFilter::make('is_adult')->label('18+'),
                Tables\Filters\TernaryFilter::make('is_restricted')->label('Запрет в РФ'),
                Tables\Filters\TernaryFilter::make('is_visible')->label('Видим'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('set_category')
                        ->label('Назначить категорию')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Forms\Components\TextInput::make('category')
                                ->label('Категория')
                                ->datalist(fn () => Tag::query()->whereNotNull('category')->distinct()->pluck('category')->filter()->values()->all())
                                ->required()
                                ->maxLength(60),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($r) => $r->forceFill(['category' => $data['category']])->save());
                            Notification::make()->title('Категория назначена')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('mark_adult')
                        ->label('Пометить 18+')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn ($r) => $r->forceFill(['is_adult' => true])->save())),
                    Tables\Actions\BulkAction::make('unmark_adult')
                        ->label('Снять 18+')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each(fn ($r) => $r->forceFill(['is_adult' => false])->save())),
                    Tables\Actions\BulkAction::make('mark_restricted')
                        ->label('Запретить в РФ')
                        ->icon('heroicon-o-globe-alt')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn ($r) => $r->forceFill(['is_restricted' => true])->save())),
                    Tables\Actions\BulkAction::make('unmark_restricted')
                        ->label('Снять запрет РФ')
                        ->action(fn ($records) => $records->each(fn ($r) => $r->forceFill(['is_restricted' => false])->save())),
                    Tables\Actions\BulkAction::make('toggle_visible')
                        ->label('Скрыть / показать')
                        ->icon('heroicon-o-eye-slash')
                        ->form([
                            Forms\Components\Toggle::make('is_visible')->label('Виден на сайте')->default(true),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($r) => $r->forceFill(['is_visible' => (bool)($data['is_visible'] ?? true)])->save());
                            Notification::make()->title('Видимость обновлена')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('export_csv')
                        ->label('Экспорт в CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            return TagResource::streamCsv($records, 'tags-' . date('Ymd-His') . '.csv');
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()->withCount('novels');
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit'   => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function streamCsv(iterable $records, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse {
        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['id', 'name', 'slug', 'category', 'description', 'color', 'icon', 'is_adult', 'is_restricted', 'is_visible', 'sort_order'], ';');
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->name,
                    $r->slug,
                    $r->category,
                    $r->description,
                    $r->color,
                    $r->icon,
                    $r->is_adult ? 1 : 0,
                    $r->is_restricted ? 1 : 0,
                    $r->is_visible ? 1 : 0,
                    $r->sort_order,
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function importCsv(string $path): array {
        $created = 0;
        $updated = 0;
        $errors  = [];

        if (!is_file($path) || !is_readable($path)) {
            return ['created' => 0, 'updated' => 0, 'errors' => ['Файл не найден или нечитаем']];
        }

        $h = fopen($path, 'r');
        if (!$h) return ['created' => 0, 'updated' => 0, 'errors' => ['Не удалось открыть файл']];

        $firstLine = fgets($h);
        if ($firstLine === false) {
            fclose($h);
            return ['created' => 0, 'updated' => 0, 'errors' => ['Пустой файл']];
        }
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delim = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($h);
        $bom = fread($h, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($h);
        }

        $headers = fgetcsv($h, 0, $delim);
        if (!$headers) {
            fclose($h);
            return ['created' => 0, 'updated' => 0, 'errors' => ['Не удалось прочитать заголовки']];
        }
        $headers = array_map(fn ($v) => trim(strtolower((string)$v)), $headers);

        $rowNum = 1;
        while (($row = fgetcsv($h, 0, $delim)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn ($v) => trim((string)$v) !== '')) === 0) continue;
            $data = array_combine($headers, array_pad($row, count($headers), null)) ?: [];
            $name = trim((string)($data['name'] ?? ''));
            if ($name === '') {
                $errors[] = "Строка $rowNum: пустое name — пропущено";
                continue;
            }
            $slug = trim((string)($data['slug'] ?? ''));
            $payload = [
                'name'          => $name,
                'category'      => isset($data['category']) ? (trim($data['category']) ?: null) : null,
                'description'   => isset($data['description']) ? (trim($data['description']) ?: null) : null,
                'color'         => isset($data['color']) ? (trim($data['color']) ?: null) : null,
                'icon'          => isset($data['icon']) ? (trim($data['icon']) ?: null) : null,
                'is_adult'      => self::boolish($data['is_adult'] ?? null),
                'is_restricted' => self::boolish($data['is_restricted'] ?? null),
                'is_visible'    => self::boolish($data['is_visible'] ?? null, true),
                'sort_order'    => (int)($data['sort_order'] ?? 0),
            ];
            if ($slug !== '') {
                $payload['slug'] = $slug;
                $tag = Tag::where('slug', $slug)->first();
            } else {
                $tag = Tag::where('name', $name)->first();
            }
            if ($tag) {
                $tag->fill($payload)->save();
                $updated++;
            } else {
                Tag::create($payload);
                $created++;
            }
        }
        fclose($h);

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
    }

    private static function boolish($v, bool $default = false): bool {
        if ($v === null) return $default;
        $s = strtolower(trim((string)$v));
        if ($s === '') return $default;
        return in_array($s, ['1', 'true', 'yes', 'y', 'да', 'on'], true);
    }
}
