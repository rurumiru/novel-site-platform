<?php
namespace App\Filament\Resources;
use App\Filament\Resources\RatingResource\Pages;
use App\Models\Rating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RatingResource extends Resource {
    protected static ?string $model = Rating::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Оценки';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel = 'Оценка';
    protected static ?string $pluralModelLabel = 'Оценки';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled(),
            Forms\Components\Select::make('novel_id')->relationship('novel', 'title')->disabled(),
            Forms\Components\TextInput::make('score')->numeric()->minValue(1)->maxValue(5)->required()->label('Оценка'),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Пользователь')->searchable(),
            Tables\Columns\TextColumn::make('novel.title')->label('Новелла')->searchable(),
            Tables\Columns\TextColumn::make('score')->label('Оценка')->badge()->color(fn ($state) => $state >= 4 ? 'success' : 'warning'),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListRatings::route('/'),
            'edit' => Pages\EditRating::route('/{record}/edit'),
        ];
    }
}
