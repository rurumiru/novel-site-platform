<?php
namespace App\Filament\Resources\NovelResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EditorsRelationManager extends RelationManager {
    protected static string $relationship = 'editors';
    protected static ?string $title = 'Редакторы';

    public function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Имя')->searchable(),
            Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
            Tables\Columns\ToggleColumn::make('can_read_paid')
                ->label('Платные главы')
                ->getStateUsing(fn ($record) => $record->pivot->can_read_paid)
                ->updateStateUsing(function ($record, $state) {
                    $record->pivot->update(['can_read_paid' => $state]);
                }),
        ])
        ->headerActions([
            Tables\Actions\AttachAction::make()
                ->preloadRecordSelect()
                ->label('Добавить редактора')
                ->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Forms\Components\Toggle::make('can_read_paid')->label('Доступ к платным главам')->default(false),
                ]),
        ])
        ->actions([
            Tables\Actions\DetachAction::make()->label('Удалить'),
        ]);
    }
}
