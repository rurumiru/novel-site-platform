<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlusPlanResource\Pages;
use App\Models\PlusPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlusPlanResource extends Resource {
    protected static ?string $model = PlusPlan::class;
    protected static ?string $navigationIcon  = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Тарифы Plus';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel       = 'Тариф Plus';
    protected static ?string $pluralModelLabel = 'Тарифы Plus';

    protected static bool $shouldRegisterNavigation = false;
    public static function canViewAny(): bool { return false; }

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Основное')->columns(2)->schema([
                Forms\Components\TextInput::make('key')
                    ->required()->maxLength(50)
                    ->label('Ключ')
                    ->helperText('monthly / days30 / days180 — используется в URL')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(100)->label('Название')
                    ->placeholder('Ежемесячная подписка'),
                Forms\Components\TextInput::make('short_label')->maxLength(80)
                    ->label('Подзаголовок')
                    ->placeholder('самая выгодная'),
                Forms\Components\TextInput::make('days')
                    ->numeric()->required()->minValue(1)
                    ->label('Длительность (дней)'),
            ]),

            Forms\Components\Section::make('Цена')->columns(3)->schema([
                Forms\Components\TextInput::make('price')
                    ->numeric()->nullable()->prefix('₽')
                    ->label('Цена')
                    ->helperText('Оставьте пустым — на сайте появится «Цена уточняется»'),
                Forms\Components\TextInput::make('currency')
                    ->default('RUB')->maxLength(8)
                    ->label('Валюта'),
                Forms\Components\TextInput::make('discount_percent')
                    ->numeric()->default(0)->suffix('%')
                    ->label('Скидка'),
            ]),

            Forms\Components\Section::make('Параметры')->columns(2)->schema([
                Forms\Components\Toggle::make('is_recurring')
                    ->label('Регулярная (автопродление)')
                    ->helperText('Если включено — отмечаем «ежемесячно» в карточке'),
                Forms\Components\Toggle::make('is_active')->default(true)
                    ->label('Активен'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()->default(0)->label('Порядок сортировки'),
            ]),

            Forms\Components\Section::make('Преимущества')->schema([
                Forms\Components\Repeater::make('features')
                    ->label('Список фич (что входит в тариф)')
                    ->simple(Forms\Components\TextInput::make('text')->required()->maxLength(200))
                    ->defaultItems(1)
                    ->reorderable()
                    ->addActionLabel('Добавить фичу'),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('key')->label('Ключ')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('name')->label('Название')->weight('bold')->searchable(),
            Tables\Columns\TextColumn::make('days')->label('Дней')->alignCenter(),
            Tables\Columns\TextColumn::make('price')
                ->label('Цена')
                ->formatStateUsing(fn ($state, PlusPlan $r) =>
                    $state ? number_format((float)$state, 0, ',', ' ') . ' ' . $r->currency : '—')
                ->alignRight(),
            Tables\Columns\TextColumn::make('discount_percent')
                ->label('Скидка')->suffix('%')
                ->alignCenter()->toggleable(),
            Tables\Columns\IconColumn::make('is_recurring')->boolean()->label('Авто'),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label('Вкл'),
        ])
        ->defaultSort('sort_order')
        ->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListPlusPlans::route('/'),
            'create' => Pages\CreatePlusPlan::route('/create'),
            'edit'   => Pages\EditPlusPlan::route('/{record}/edit'),
        ];
    }
}
