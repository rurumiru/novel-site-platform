<?php
namespace App\Filament\Resources;

use App\Filament\Resources\NovelPromotionResource\Pages;
use App\Models\NovelPromotion;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class NovelPromotionResource extends Resource {
    protected static ?string $model           = NovelPromotion::class;
    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Продвижения';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel      = 'Продвижение';
    protected static ?string $pluralModelLabel = 'Продвижения';

    public static function canAccess(): bool {
        return Schema::hasTable('novel_promotions');
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable()->width('60px'),

                Tables\Columns\TextColumn::make('novel.title')
                    ->label('Новелла')
                    ->searchable()
                    ->weight('bold')
                    ->limit(35),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Автор')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'homepage_featured' => 'Главная',
                        'catalog_top'       => 'Топ каталога',
                        default             => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'homepage_featured' ? 'warning' : 'info'),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Оплачено')
                    ->suffix(' мон.'),

                Tables\Columns\TextColumn::make('starts_at')->label('Начало')->dateTime('d.m.Y')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->label('Конец')->dateTime('d.m.Y')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'active'    => 'success',
                        'expired'   => 'gray',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active'    => 'Активно',
                        'expired'   => 'Истекло',
                        'cancelled' => 'Отменено',
                        default     => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(['active' => 'Активные', 'expired' => 'Истекшие', 'cancelled' => 'Отменённые']),
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(['homepage_featured' => 'Главная', 'catalog_top' => 'Топ каталога']),
            ])
            ->actions([
                Action::make('cancel')
                    ->label('Отменить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (NovelPromotion $r) => $r->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (NovelPromotion $r) {
                        $r->update(['status' => 'cancelled']);
                        Notification::make()->title('Продвижение отменено')->warning()->send();
                    }),
            ])
            ->paginated([25, 50]);
    }

    public static function canCreate(): bool { return false; }
    public static function getRelations(): array { return []; }

    public static function getPages(): array {
        return [
            'index' => Pages\ListNovelPromotions::route('/'),
        ];
    }
}
