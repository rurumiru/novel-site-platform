<?php
namespace App\Filament\Resources\TranslationTeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NovelsRelationManager extends RelationManager {
    protected static string $relationship = 'novels';
    protected static ?string $title = 'Новеллы';
    protected static ?string $modelLabel = 'новеллу';
    protected static ?string $pluralModelLabel = 'Новеллы';

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('team_revenue_share')
                ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                ->label('Доля команды от дохода новеллы')->required()->default(70),
            Forms\Components\Toggle::make('is_primary')
                ->label('Основная команда для этой новеллы')->default(false)->inline(false),
            Forms\Components\Toggle::make('show_credits')
                ->label('Показывать команду в кредитах')->default(true)->inline(false),
        ]);
    }

    public function table(Table $table): Table {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()->label('Новелла')->weight('semibold'),
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('Автор')->color('gray'),
                Tables\Columns\TextColumn::make('pivot.team_revenue_share')
                    ->suffix('%')->label('Доля команды')->alignCenter()->badge()->color('success'),
                Tables\Columns\IconColumn::make('pivot.is_primary')
                    ->boolean()->label('Основная'),
                Tables\Columns\IconColumn::make('pivot.show_credits')
                    ->boolean()->label('Кредиты')->trueColor('info'),
                Tables\Columns\TextColumn::make('chapters_count')
                    ->counts('chapters')->label('Глав')->alignCenter(),
                Tables\Columns\TextColumn::make('pivot.assigned_at')
                    ->date('d.m.Y')->label('Назначена'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Привязать новеллу')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title'])
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('team_revenue_share')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                            ->label('Доля команды')->required()->default(70),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Основная команда')->default(false)->inline(false),
                        Forms\Components\Toggle::make('show_credits')
                            ->label('Показывать кредиты')->default(true)->inline(false),
                    ])
                    ->mutateFormDataUsing(fn(array $data): array => array_merge($data, [
                        'assigned_at' => now(),
                    ])),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_pivot')
                    ->label('Настройки')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->form([
                        Forms\Components\TextInput::make('team_revenue_share')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                            ->label('Доля команды')->required(),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Основная команда')->inline(false),
                        Forms\Components\Toggle::make('show_credits')
                            ->label('Показывать кредиты')->inline(false),
                    ])
                    ->mountUsing(function (Forms\Form $form, $record): void {
                        $form->fill([
                            'team_revenue_share' => $record->pivot->team_revenue_share ?? 70,
                            'is_primary'         => (bool)($record->pivot->is_primary ?? false),
                            'show_credits'       => (bool)($record->pivot->show_credits ?? true),
                        ]);
                    })
                    ->action(function ($record, array $data): void {
                        $this->getOwnerRecord()->novels()->updateExistingPivot($record->id, [
                            'team_revenue_share' => $data['team_revenue_share'],
                            'is_primary'         => $data['is_primary'],
                            'show_credits'       => $data['show_credits'],
                        ]);
                    }),
                Tables\Actions\DetachAction::make()->label('Убрать'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()->label('Убрать выбранные'),
            ]);
    }
}
