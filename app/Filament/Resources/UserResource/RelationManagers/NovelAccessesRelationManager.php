<?php
namespace App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NovelAccessesRelationManager extends RelationManager {
    protected static string $relationship = 'novelAccesses';
    protected static ?string $title = 'Доступ к закрытым новеллам';

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('novel_id')
                ->relationship('novel', 'title')
                ->required()
                ->label('Новелла'),
            Forms\Components\Toggle::make('is_full_access')
                ->label('Полный доступ')
                ->live(),
            Forms\Components\TextInput::make('chapters_count')
                ->numeric()
                ->label('Кол-во открытых глав')
                ->hidden(fn (Forms\Get $get) => $get('is_full_access'))
                ->default(0),
        ]);
    }

    public function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('novel_id')->label('ID Новеллы'),
            Tables\Columns\IconColumn::make('is_full_access')->boolean()->label('Full'),
            Tables\Columns\TextColumn::make('chapters_count')->label('Глав открыто'),
        ])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
