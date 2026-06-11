<?php
namespace App\Filament\Resources;

use App\Filament\Resources\NovelPromotionPackageResource\Pages;
use App\Models\NovelPromotionPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class NovelPromotionPackageResource extends Resource {
    protected static ?string $model           = NovelPromotionPackage::class;
    protected static ?string $navigationIcon  = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Пакеты продвижения';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 6;
    protected static ?string $modelLabel      = 'Пакет продвижения';
    protected static ?string $pluralModelLabel = 'Пакеты продвижения';

    public static function canAccess(): bool {
        return Schema::hasTable('novel_promotion_packages');
    }

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(100),

            Forms\Components\Select::make('type')
                ->label('Тип')
                ->required()
                ->options([
                    'homepage_featured' => 'Главная страница',
                    'catalog_top'       => 'Топ каталога',
                ]),

            Forms\Components\TextInput::make('duration_days')
                ->label('Длительность (дни)')
                ->numeric()
                ->required()
                ->minValue(1),

            Forms\Components\TextInput::make('price_coins')
                ->label('Цена (монет)')
                ->numeric()
                ->required()
                ->minValue(1),

            Forms\Components\TextInput::make('max_slots')
                ->label('Макс. слотов')
                ->numeric()
                ->default(5)
                ->required()
                ->minValue(1),

            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->rows(2)
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label('Активен')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'homepage_featured' => 'Главная',
                        'catalog_top'       => 'Топ каталога',
                        default             => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'homepage_featured' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('duration_days')->label('Дней')->suffix(' д'),
                Tables\Columns\TextColumn::make('price_coins')->label('Цена')->suffix(' мон.'),
                Tables\Columns\TextColumn::make('max_slots')->label('Слоты'),
                Tables\Columns\IconColumn::make('is_active')->label('Активен')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListNovelPromotionPackages::route('/'),
            'create' => Pages\CreateNovelPromotionPackage::route('/create'),
            'edit'   => Pages\EditNovelPromotionPackage::route('/{record}/edit'),
        ];
    }
}
