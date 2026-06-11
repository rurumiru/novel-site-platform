<?php
namespace App\Filament\Resources\TranslationTeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager {
    protected static string $relationship = 'members';
    protected static ?string $title = 'Участники';
    protected static ?string $modelLabel = 'участника';
    protected static ?string $pluralModelLabel = 'Участники';

    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()->preload()->required()->label('Пользователь')
                ->columnSpanFull(),
            Forms\Components\Select::make('role')
                ->options([
                    'leader'      => 'Лидер',
                    'coordinator' => 'Координатор',
                    'translator'  => 'Переводчик',
                    'editor'      => 'Редактор',
                    'typesetter'  => 'Тайпсеттер',
                    'illustrator' => 'Иллюстратор',
                    'tlc'         => 'TLC',
                    'beta'        => 'Бета-ридер',
                    'member'      => 'Участник',
                ])->required()->label('Роль'),
            Forms\Components\TextInput::make('title')
                ->maxLength(120)->label('Кастомный титул')->placeholder('Оставьте пустым — будет роль'),
            Forms\Components\TextInput::make('revenue_share')
                ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                ->label('Доля доходов')->default(0),
            Forms\Components\Toggle::make('is_active')->label('Активен')->default(true)->inline(false),
            Forms\Components\Toggle::make('display_on_profile')->label('Показывать на странице команды')->default(true)->inline(false),

            Forms\Components\Section::make('Привилегии')->schema([
                Forms\Components\Toggle::make('can_assign_novels')
                    ->label('Назначать новеллы')->inline(false)->default(false),
                Forms\Components\Toggle::make('can_invite_members')
                    ->label('Приглашать участников')->inline(false)->default(false),
                Forms\Components\Toggle::make('can_manage_payouts')
                    ->label('Управлять выплатами')->inline(false)->default(false),
                Forms\Components\Toggle::make('can_publish')
                    ->label('Публиковать главы')->inline(false)->default(false),
                Forms\Components\Toggle::make('can_edit_any_chapter')
                    ->label('Редактировать любые главы')->inline(false)->default(false),
            ])->columns(2)->collapsible(),

            Forms\Components\Textarea::make('note')
                ->rows(2)->label('Внутренняя заметка')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()->label('Участник')->weight('semibold'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()->label('Роль')
                    ->formatStateUsing(fn($s) => match($s) {
                        'leader'      => 'Лидер',
                        'coordinator' => 'Координатор',
                        'translator'  => 'Переводчик',
                        'editor'      => 'Редактор',
                        'typesetter'  => 'Тайпсеттер',
                        'illustrator' => 'Иллюстратор',
                        'tlc'         => 'TLC',
                        'beta'        => 'Бета-ридер',
                        'member'      => 'Участник',
                        default       => $s,
                    })
                    ->color(fn($s) => match($s) {
                        'leader'      => 'danger',
                        'coordinator' => 'warning',
                        'translator'  => 'success',
                        'editor'      => 'info',
                        'typesetter'  => 'primary',
                        'illustrator' => 'gray',
                        'tlc'         => 'gray',
                        'beta'        => 'gray',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Титул')->color('gray')->placeholder('—'),
                Tables\Columns\TextColumn::make('revenue_share')
                    ->suffix('%')->label('Доля')->alignCenter()->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()->label('Акт.'),
                Tables\Columns\IconColumn::make('can_publish')
                    ->boolean()->label('Публ.')->trueColor('success'),
                Tables\Columns\IconColumn::make('can_edit_any_chapter')
                    ->boolean()->label('Ред.')->trueColor('info'),
                Tables\Columns\IconColumn::make('can_invite_members')
                    ->boolean()->label('Инв.')->trueColor('warning'),
                Tables\Columns\TextColumn::make('joined_at')
                    ->date('d.m.Y')->label('Вступил')->sortable(),
            ])
            ->defaultSort('joined_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'leader'      => 'Лидер',
                        'coordinator' => 'Координатор',
                        'translator'  => 'Переводчик',
                        'editor'      => 'Редактор',
                        'typesetter'  => 'Тайпсеттер',
                        'illustrator' => 'Иллюстратор',
                        'tlc'         => 'TLC',
                        'beta'        => 'Бета-ридер',
                        'member'      => 'Участник',
                    ])->label('Роль'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Только активные'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить участника')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['joined_at'] ??= now();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\Action::make('kick')
                    ->label('Исключить')->color('danger')
                    ->icon('heroicon-o-user-minus')
                    ->modalHeading('Исключить участника')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Причина исключения')->rows(3)->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'is_active' => false,
                            'left_at'   => now(),
                            'note'      => ($record->note ? $record->note . "\n" : '') . '[Исключён] ' . $data['reason'],
                        ]);
                    }),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')->color('warning')
                        ->requiresConfirmation()
                        ->action(fn($r) => $r->each->update(['is_active' => false, 'left_at' => now()])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
