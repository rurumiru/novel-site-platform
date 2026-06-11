<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ChapterPurchaseResource\Pages;
use App\Models\ChapterPurchase;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ChapterPurchaseResource extends Resource {

    public static function canAccess(): bool {
        return Schema::hasTable('chapter_purchases');
    }
    protected static ?string $model = ChapterPurchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Покупки глав';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel = 'Покупка главы';
    protected static ?string $pluralModelLabel = 'Покупки глав';

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Покупатель')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (ChapterPurchase $r) => route('filament.admin.resources.users.edit', $r->user_id), true),

                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('Глава')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('chapter.novel.title')
                    ->label('Новелла')
                    ->searchable()
                    ->limit(35)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price_paid')
                    ->label('Уплачено')
                    ->formatStateUsing(fn ($state) => $state . ' ₽')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата покупки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('paid_only')
                    ->label('Только платные (> 0 ₽)')
                    ->query(fn (Builder $q) => $q->where('price_paid', '>', 0))
                    ->toggle(),
            ])
            ->searchable()
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array { return []; }

    public static function canCreate(): bool { return false; }

    public static function getPages(): array {
        return [
            'index' => Pages\ListChapterPurchases::route('/'),
        ];
    }
}
