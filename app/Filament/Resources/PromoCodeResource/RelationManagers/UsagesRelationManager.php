<?php
namespace App\Filament\Resources\PromoCodeResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager {
    protected static string $relationship = 'usages';
    protected static ?string $title = 'Использования';

    public function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')
                ->label('Пользователь')
                ->searchable()
                ->weight('bold'),
            Tables\Columns\TextColumn::make('user.email')
                ->label('Email')
                ->searchable()
                ->toggleable(),
            Tables\Columns\IconColumn::make('applied_at')
                ->label('Применено')
                ->boolean()
                ->getStateUsing(fn ($record) => $record->applied_at !== null)
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-clock')
                ->trueColor('success')
                ->falseColor('warning'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime('d.m.Y H:i')
                ->label('Дата активации')
                ->sortable(),
            Tables\Columns\TextColumn::make('applied_at')
                ->dateTime('d.m.Y H:i')
                ->label('Дата применения')
                ->placeholder('Ещё не использована')
                ->sortable()
                ->toggleable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->actions([
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
