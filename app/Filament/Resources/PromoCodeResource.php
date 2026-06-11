<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromoCodeResource extends Resource {
    protected static ?string $model = PromoCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Промокоды';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel = 'Промокод';
    protected static ?string $pluralModelLabel = 'Промокоды';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Основное')->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label('Код')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->helperText('Латиница, цифры, дефисы. Будет приведён к верхнему регистру.')
                    ->columnSpan(1),
                Forms\Components\Select::make('type')
                    ->required()
                    ->label('Тип')
                    ->options([
                        'balance'       => 'Баланс — начисляет монеты',
                        'discount'      => 'Скидка — % на покупку главы',
                        'free_novel'    => 'Бесплатная новелла — открывает все главы',
                        'free_chapters' => 'Бесплатные главы — открывает N глав',
                    ])
                    ->live()
                    ->columnSpan(1),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->label(fn (Forms\Get $get) => match ($get('type')) {
                        'discount' => 'Скидка (%)',
                        'balance'  => 'Сумма (₽)',
                        default    => 'Значение',
                    })
                    ->helperText(fn (Forms\Get $get) => match ($get('type')) {
                        'discount' => 'Процент скидки (1–100)',
                        'balance'  => 'Количество монет',
                        default    => '',
                    })
                    ->minValue(1)
                    ->maxValue(fn (Forms\Get $get) => $get('type') === 'discount' ? 100 : null)
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['balance', 'discount']))
                    ->columnSpan(1),
                Forms\Components\TextInput::make('chapters_count')
                    ->required()
                    ->numeric()
                    ->label('Количество глав')
                    ->helperText('Сколько платных глав открыть (по порядку, начиная с первой неоткрытой)')
                    ->minValue(1)
                    ->default(1)
                    ->visible(fn (Forms\Get $get) => $get('type') === 'free_chapters')
                    ->columnSpan(1),
                Forms\Components\Select::make('novel_id')
                    ->relationship('novel', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Новелла')
                    ->helperText(fn (Forms\Get $get) => match ($get('type')) {
                        'free_novel'    => 'Обязательно — все платные главы этой новеллы будут открыты',
                        'free_chapters' => 'Если указана — главы только этой новеллы. Пусто — любая новелла',
                        'discount'      => 'Если указана — скидка только на главы этой новеллы',
                        default         => '',
                    })
                    ->required(fn (Forms\Get $get) => $get('type') === 'free_novel')
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['free_novel', 'free_chapters', 'discount']))
                    ->columnSpan(1),
            ])->columns(2),

            Forms\Components\Section::make('Лимиты')->schema([
                Forms\Components\TextInput::make('max_uses')
                    ->numeric()
                    ->label('Макс. использований')
                    ->helperText('Пусто = безлимитно')
                    ->nullable()
                    ->minValue(1),
                Forms\Components\TextInput::make('max_uses_per_user')
                    ->numeric()
                    ->required()
                    ->label('Макс. на пользователя')
                    ->default(1)
                    ->minValue(1),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Начало действия')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Истекает')
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Дополнительно')->schema([
                Forms\Components\Textarea::make('description')
                    ->label('Заметка (для админов)')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->collapsible()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')
                ->label('Код')
                ->searchable()
                ->weight('bold')
                ->copyable()
                ->fontFamily('mono'),
            Tables\Columns\TextColumn::make('type')
                ->label('Тип')
                ->badge()
                ->formatStateUsing(fn ($state) => match ($state) {
                    'balance'       => 'Баланс',
                    'discount'      => 'Скидка',
                    'free_novel'    => 'Вся новелла',
                    'free_chapters' => 'N глав',
                    default         => $state,
                })
                ->color(fn ($state) => match ($state) {
                    'balance'       => 'success',
                    'discount'      => 'warning',
                    'free_novel'    => 'info',
                    'free_chapters' => 'primary',
                    default         => 'gray',
                }),
            Tables\Columns\TextColumn::make('effect_label')
                ->label('Эффект')
                ->badge()
                ->color('gray'),
            Tables\Columns\TextColumn::make('novel.title')
                ->label('Новелла')
                ->limit(25)
                ->placeholder('—')
                ->toggleable(),
            Tables\Columns\TextColumn::make('uses_count')
                ->label('Исп-й')
                ->formatStateUsing(fn ($state, $record) => $record->max_uses
                    ? "{$state}/{$record->max_uses}"
                    : $state
                )
                ->sortable(),
            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->label('Акт.'),
            Tables\Columns\TextColumn::make('expires_at')
                ->dateTime('d.m.Y')
                ->label('Истекает')
                ->placeholder('∞')
                ->toggleable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime('d.m.Y')
                ->label('Создан')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('type')
                ->options([
                    'balance'       => 'Баланс',
                    'discount'      => 'Скидка',
                    'free_novel'    => 'Вся новелла',
                    'free_chapters' => 'N глав',
                ])
                ->label('Тип'),
            Tables\Filters\TernaryFilter::make('is_active')->label('Активен'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getRelations(): array {
        return [
            PromoCodeResource\RelationManagers\UsagesRelationManager::class,
        ];
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit'   => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
