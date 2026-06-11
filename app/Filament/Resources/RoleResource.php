<?php
namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource {
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Роли и Права';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel = 'Роль';
    protected static ?string $pluralModelLabel = 'Роли';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Роль')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название роли (код)')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('guard_name')
                    ->default('web')
                    ->disabled(),
            ]),

            Forms\Components\Section::make('Права доступа')->schema([
                Forms\Components\CheckboxList::make('permissions')
                    ->label('Разрешенные действия')
                    ->relationship('permissions', 'name')
                    ->columns(2)
                    ->bulkToggleable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Роль')->badge()->color('info'),
            Tables\Columns\TextColumn::make('permissions_count')->counts('permissions')->label('Прав'),
            Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Пользователей'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
