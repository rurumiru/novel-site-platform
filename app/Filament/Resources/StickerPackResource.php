<?php
namespace App\Filament\Resources;

use App\Models\StickerPack;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class StickerPackResource extends Resource
{
    protected static ?string $model = StickerPack::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Наборы стикеров';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int    $navigationSort  = 30;
    protected static ?string $modelLabel = 'Набор стикеров';
    protected static ?string $pluralModelLabel = 'Наборы стикеров';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Набор')->schema([
                Forms\Components\TextInput::make('name')->label('Название')->required(),
                Forms\Components\TextInput::make('slug')->label('Slug (латиница)')->required()
                    ->helperText('Используется в URL-параметрах. a-z, 0-9, -, _')
                    ->regex('/^[a-z0-9_\-]+$/'),
                Forms\Components\FileUpload::make('cover_image')
                    ->image()->disk('s3')->directory('stickers/covers')->visibility('public')
                    ->label('Обложка набора (необязательно)'),
                Forms\Components\Toggle::make('is_active')->default(true)->label('Активен'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Порядок'),
            ])->columns(2),

            Forms\Components\Section::make('Стикеры')
                ->description('Загружайте по одному или сразу пачку — они будут добавлены в набор автоматически.')
                ->schema([
                    Forms\Components\FileUpload::make('bulk_uploads')
                        ->label('Загрузить стикеры')
                        ->multiple()
                        ->image()
                        ->disk('s3')->directory('stickers/img')->visibility('public')
                        ->maxSize(2048)
                        ->helperText('PNG/WEBP, до 2 МБ. Quad-shape (квадратные) лучше всего.')
                        ->dehydrated(false)
                        ->afterStateUpdated(function ($state, $record) {
                            if (!$record || empty($state)) return;
                            foreach ((array) $state as $path) {
                                if (is_string($path) && $path) {
                                    $base = pathinfo($path, PATHINFO_FILENAME);
                                    $slug = \Illuminate\Support\Str::slug($base . '-' . substr(md5($path), 0, 6));
                                    \App\Models\Sticker::create([
                                        'pack_id'    => $record->id,
                                        'name'       => $base,
                                        'slug'       => $slug,
                                        'image_path' => $path,
                                        'is_active'  => true,
                                    ]);
                                }
                            }
                            \Illuminate\Support\Facades\Cache::forget('eri-stickers-map');
                        })
                        ->visible(fn ($record) => (bool) $record),
                ])->visible(fn ($record) => (bool) $record),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('cover_image')->disk('s3')->label('Обложка'),
            Tables\Columns\TextColumn::make('name')->label('Название')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('slug')->label('Slug')->fontFamily('mono'),
            Tables\Columns\TextColumn::make('stickers_count')->counts('stickers')->label('Стикеров'),
            Tables\Columns\ToggleColumn::make('is_active')->label('Активен'),
            Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable(),
        ])
        ->defaultSort('sort_order')
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => StickerPackResource\Pages\ListStickerPacks::route('/'),
            'create' => StickerPackResource\Pages\CreateStickerPack::route('/create'),
            'edit'   => StickerPackResource\Pages\EditStickerPack::route('/{record}/edit'),
        ];
    }
}
