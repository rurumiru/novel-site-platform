<?php
namespace App\Filament\Resources;

use App\Filament\Resources\TranslationTeamResource\Pages;
use App\Filament\Resources\TranslationTeamResource\RelationManagers;
use App\Models\TranslationTeam;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class TranslationTeamResource extends Resource {
    protected static ?string $model = TranslationTeam::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Команды';
    protected static ?string $navigationGroup = 'Сообщество';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Команда';
    protected static ?string $pluralModelLabel = 'Команды переводчиков';

    public static function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\Section::make('Основное')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(120)->label('Название'),
                Forms\Components\TextInput::make('slug')
                    ->required()->maxLength(140)->label('Slug')->unique(ignoreRecord: true),
                Forms\Components\Select::make('leader_id')
                    ->relationship('leader', 'name')
                    ->searchable()->preload()->required()->label('Лидер'),
                Forms\Components\TextInput::make('mission')
                    ->maxLength(280)->label('Девиз / миссия'),
                Forms\Components\Textarea::make('description')
                    ->rows(4)->label('Описание'),
            ])->columns(2),

            Forms\Components\Section::make('Статус и настройки')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'active'     => '🔵 Активна',
                        'recruiting' => '🟢 Набор открыт',
                        'closed'     => '⚫ Закрыта',
                        'on_hiatus'  => '⏸ Пауза',
                        'disbanded'  => '❌ Распущена',
                    ])->required()->label('Статус'),
                Forms\Components\Select::make('application_mode')
                    ->options([
                        'invite_only' => '🔒 Только по приглашению',
                        'open'        => '📬 Открытые заявки',
                        'closed'      => '🚫 Закрыто',
                    ])->required()->label('Режим вступления'),
                Forms\Components\Toggle::make('is_official')
                    ->label('Официальная команда')->helperText('Показывает бейдж ✓ и поднимает в списке'),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Показывать на витрине')->helperText('Приоритет в каталоге'),
                Forms\Components\TextInput::make('leader_share')
                    ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                    ->label('Доля лидера по умолчанию'),
                Forms\Components\TextInput::make('min_trust_level')
                    ->numeric()->minValue(0)->maxValue(4)
                    ->label('Минимальный trust level для заявки'),
            ])->columns(2),

            Forms\Components\Section::make('Финансы')->schema([
                Forms\Components\TextInput::make('balance')
                    ->numeric()->suffix('₽')->label('Баланс команды'),
                Forms\Components\TextInput::make('total_earned')
                    ->numeric()->suffix('₽')->label('Всего заработано')->disabled(),
            ])->columns(2)->collapsible()->collapsed(),

            Forms\Components\Section::make('Контакты')->schema([
                Forms\Components\TextInput::make('discord')->url()->label('Discord')->prefix('https://'),
                Forms\Components\TextInput::make('telegram')->url()->label('Telegram')->prefix('https://'),
                Forms\Components\TextInput::make('vk')->url()->label('ВКонтакте')->prefix('https://'),
                Forms\Components\TextInput::make('website')->url()->label('Сайт')->prefix('https://'),
            ])->columns(2)->collapsible()->collapsed(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable()->weight('bold')->label('Название')
                    ->description(fn($r) => $r->mission ?? ''),
                Tables\Columns\TextColumn::make('leader.name')
                    ->searchable()->label('Лидер')->color('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()->label('Статус')
                    ->formatStateUsing(fn($s) => match($s) {
                        'active'     => 'Активна',
                        'recruiting' => 'Набор',
                        'closed'     => 'Закрыта',
                        'on_hiatus'  => 'Пауза',
                        'disbanded'  => 'Распущена',
                        default      => $s,
                    })
                    ->color(fn($s) => match($s) {
                        'active'     => 'info',
                        'recruiting' => 'success',
                        'closed'     => 'gray',
                        'on_hiatus'  => 'warning',
                        'disbanded'  => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('active_members_count')
                    ->counts('activeMembers')->label('Участники')->alignCenter(),
                Tables\Columns\TextColumn::make('novels_count')
                    ->counts('novels')->label('Новеллы')->alignCenter(),
                Tables\Columns\TextColumn::make('total_earned')
                    ->formatStateUsing(fn($s) => number_format((int)$s, 0, '.', ' ') . ' ₽')
                    ->label('Заработано')->sortable(),
                Tables\Columns\IconColumn::make('is_official')
                    ->boolean()->label('Офиц.')->trueIcon('heroicon-o-check-badge')->trueColor('info'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()->label('Витрина')->trueIcon('heroicon-o-star')->trueColor('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y')->sortable()->label('Создана')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'     => 'Активна',
                        'recruiting' => 'Набор',
                        'closed'     => 'Закрыта',
                        'on_hiatus'  => 'Пауза',
                        'disbanded'  => 'Распущена',
                    ])->label('Статус'),
                Tables\Filters\TernaryFilter::make('is_official')->label('Официальные'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('На витрине'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_site')
                    ->label('На сайте')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($r) => route('teams.show', $r->slug))->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('make_official')
                        ->label('Сделать официальными')->icon('heroicon-o-check-badge')
                        ->action(fn($r) => $r->each->update(['is_official' => true])),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('На витрину')->icon('heroicon-o-star')
                        ->action(fn($r) => $r->each->update(['is_featured' => true])),
                    Tables\Actions\BulkAction::make('disband')
                        ->label('Распустить')->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($r) => $r->each->update(['status' => 'disbanded'])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Infolists\Components\Section::make('Информация')->schema([
                Infolists\Components\TextEntry::make('name')->label('Название'),
                Infolists\Components\TextEntry::make('slug')->label('Slug')->copyable(),
                Infolists\Components\TextEntry::make('leader.name')->label('Лидер'),
                Infolists\Components\TextEntry::make('status')->label('Статус'),
                Infolists\Components\TextEntry::make('mission')->label('Девиз'),
                Infolists\Components\TextEntry::make('description')->label('Описание')->columnSpanFull(),
            ])->columns(3),
            Infolists\Components\Section::make('Финансы')->schema([
                Infolists\Components\TextEntry::make('balance')->label('Баланс')->suffix(' ₽'),
                Infolists\Components\TextEntry::make('total_earned')->label('Заработано')->suffix(' ₽'),
                Infolists\Components\TextEntry::make('leader_share')->label('Доля лидера')->suffix('%'),
            ])->columns(3),
        ]);
    }

    public static function getRelationManagers(): array {
        return [
            RelationManagers\MembersRelationManager::class,
            RelationManagers\NovelsRelationManager::class,
        ];
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListTranslationTeams::route('/'),
            'create' => Pages\CreateTranslationTeam::route('/create'),
            'edit'   => Pages\EditTranslationTeam::route('/{record}/edit'),
        ];
    }
}
