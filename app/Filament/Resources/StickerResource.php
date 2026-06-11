<?php
namespace App\Filament\Resources;

use App\Models\Sticker;
use App\Models\StickerPack;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class StickerResource extends Resource
{
    protected static ?string $model = Sticker::class;
    protected static ?string $navigationIcon = 'heroicon-o-face-smile';
    protected static ?string $navigationLabel = 'Стикеры';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int    $navigationSort  = 31;
    protected static ?string $modelLabel = 'Стикер';
    protected static ?string $pluralModelLabel = 'Стикеры';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('pack_id')
                ->label('Набор')
                ->options(fn () => StickerPack::pluck('name', 'id'))
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('name')->label('Название')->maxLength(120),
            Forms\Components\TextInput::make('slug')->label('Slug (для [sticker:slug])')->required()
                ->regex('/^[a-z0-9_\-]+$/')
                ->unique(ignoreRecord: true),
            Forms\Components\FileUpload::make('image_path')
                ->label('Изображение')
                ->image()->disk('s3')->directory('stickers/img')->visibility('public')
                ->required()->maxSize(2048),
            Forms\Components\Toggle::make('is_active')->default(true)->label('Активен'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Порядок'),
        ])->columns(2);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image_path')->disk('s3')->label('Стикер')->size(56),
            Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
            Tables\Columns\TextColumn::make('slug')->label('Slug')->fontFamily('mono')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('pack.name')->label('Набор')->sortable(),
            Tables\Columns\ToggleColumn::make('is_active')->label('Активен'),
            Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('pack_id')->label('Набор')
                ->options(fn () => StickerPack::pluck('name', 'id')),
            Tables\Filters\TernaryFilter::make('is_active')->label('Активные'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => StickerResource\Pages\ListStickers::route('/'),
            'create' => StickerResource\Pages\CreateSticker::route('/create'),
            'edit'   => StickerResource\Pages\EditSticker::route('/{record}/edit'),
        ];
    }
}
