<?php
namespace App\Filament\Resources;
use App\Filament\Resources\AdBannerResource\Pages;
use App\Models\AdBanner;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class AdBannerResource extends Resource {
    protected static ?string $model = AdBanner::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Рекламные баннеры';
    protected static ?string $navigationGroup = 'Сайт';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel = 'Рекламный баннер';
    protected static ?string $pluralModelLabel = 'Рекламные баннеры';

    public static function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Название (для админа)'),
            Forms\Components\FileUpload::make('image')->image()->disk('s3')->directory('ads')->visibility('public')->required()->label('Изображение'),
            Forms\Components\TextInput::make('url')->url()->label('Ссылка'),
            Forms\Components\Toggle::make('is_active')->default(true)->label('Активен'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Порядок'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->disk('s3'),
            Tables\Columns\TextColumn::make('title'),
            Tables\Columns\ToggleColumn::make('is_active'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListAdBanners::route('/'),
            'create' => Pages\CreateAdBanner::route('/create'),
            'edit' => Pages\EditAdBanner::route('/{record}/edit'),
        ];
    }
}
