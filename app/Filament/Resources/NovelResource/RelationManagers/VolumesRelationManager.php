<?php
namespace App\Filament\Resources\NovelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class VolumesRelationManager extends RelationManager {
    protected static string $relationship = 'volumes';
    protected static ?string $title = 'Тома';
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Название тома')
                ->required()
                ->maxLength(255)
                ->placeholder('Например: Том 1. Начало'),
            
            Forms\Components\TextInput::make('sort_order')
                ->label('Порядок сортировки')
                ->numeric()
                ->default(0)
                ->required(),
        ]);
    }

    public function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('chapters_count')
                    ->counts('chapters')
                    ->label('Кол-во глав')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y')
                    ->label('Создан')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить том'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_chapters')
                    ->label('Главы')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->modalHeading(fn ($record) => 'Главы тома: ' . $record->title)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->modalContent(function ($record) {
                        $chapters = $record->chapters()->orderBy('sort_order')->get();
                        return view('filament.partials.volume-chapters-modal', compact('chapters'));
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
