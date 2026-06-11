<?php
namespace App\Filament\Resources;
use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MessageResource extends Resource {
    protected static ?string $model = Message::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Логи чатов';
    protected static ?string $navigationGroup = 'Модерация';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel = 'Сообщение';
    protected static ?string $pluralModelLabel = 'Логи чатов';

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('sender.name')->label('Отправитель')->searchable(),
            Tables\Columns\TextColumn::make('receiver.name')->label('Получатель')->searchable(),
            Tables\Columns\TextColumn::make('message')->label('Сообщение')->limit(50)->searchable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Время'),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array {
        return ['index' => Pages\ListMessages::route('/')];
    }
}
