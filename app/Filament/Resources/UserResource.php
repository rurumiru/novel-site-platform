<?php
namespace App\Filament\Resources;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\NovelAccessesRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource {
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Пользователи';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\Section::make('Профиль')->schema([
                Forms\Components\FileUpload::make('avatar')->image()->directory('avatars')->disk('s3')->visibility('public')->label('Аватар')->avatar(),
                Forms\Components\TextInput::make('name')->required()->label('Имя'),
                Forms\Components\TextInput::make('username')->label('Логин'),
                Forms\Components\TextInput::make('email')->email()->required()->label('Email'),
                Forms\Components\TextInput::make('social_link')->label('Соцсеть'),
                Forms\Components\TextInput::make('password')->password()->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)->dehydrated(fn($state) => filled($state))->label('Новый пароль'),
            ])->columns(2),
            Forms\Components\Section::make('Модерация')->schema([
                Forms\Components\Textarea::make('bio')->disabled()->label('Текущее описание'),
                Forms\Components\Textarea::make('bio_pending')->label('Описание на модерации'),
                Forms\Components\TextInput::make('donation_link')->label('Ссылка на донат')->url(),
                Forms\Components\Toggle::make('is_donation_link_approved')->label('Донат-ссылка одобрена'),
            ])->columns(2)->collapsible(),
            Forms\Components\Section::make('Права и финансы')->schema([
                Forms\Components\TextInput::make('balance')->numeric()->label('Баланс (₽)'),
                Forms\Components\Toggle::make('can_create_novels')->label('Может создавать новеллы'),
                Forms\Components\Toggle::make('can_set_banner')->label('Может ставить баннер'),
                Forms\Components\Select::make('roles')->relationship('roles', 'name')->multiple()->preload()->label('Роли'),
            ])->columns(2),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('avatar')->disk('s3')->circular()->width(36)->height(36)->label(''),
            Tables\Columns\TextColumn::make('name')->searchable()->weight('bold')->label('Имя'),
            Tables\Columns\TextColumn::make('username')->searchable()->label('Логин')->prefix('@')->color('gray'),
            Tables\Columns\TextColumn::make('email')->searchable()->label('Email')->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('roles.name')->badge()->label('Роли'),
            Tables\Columns\TextColumn::make('novels_count')->counts('novels')->label('Новелл')->sortable(),
            Tables\Columns\TextColumn::make('balance')->label('Баланс')->suffix(' ₽')->sortable(),
            Tables\Columns\IconColumn::make('email_verified_at')->boolean()->label('Почта'),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y')->label('Рег.')->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('roles')->relationship('roles', 'name')->label('Роль')->preload(),
        ])
        ->actions([
            Tables\Actions\Action::make('approve_bio')
                ->icon('heroicon-o-check')
                ->label('Одобрить описание')
                ->visible(fn(User $r) => $r->bio_pending && $r->bio_pending !== $r->bio)
                ->action(fn(User $r) => $r->update(['bio' => $r->bio_pending, 'bio_pending' => null]))
                ->color('success'),
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array {
        return [NovelAccessesRelationManager::class];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
