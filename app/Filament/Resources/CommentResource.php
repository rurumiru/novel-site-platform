<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CommentResource extends Resource {
    protected static ?string $model = Comment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Комментарии';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel = 'Комментарий';
    protected static ?string $pluralModelLabel = 'Комментарии';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')
                ->default(fn () => Auth::id())
                ->required(),

            Forms\Components\MorphToSelect::make('commentable')
                ->label('Где оставить')
                ->types([
                    Forms\Components\MorphToSelect\Type::make(Novel::class)
                        ->titleAttribute('title')
                        ->label('Новелла'),
                    Forms\Components\MorphToSelect\Type::make(Chapter::class)
                        ->titleAttribute('title')
                        ->label('Глава'),
                ])
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Textarea::make('content')
                ->label('Текст комментария')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')
                ->label('Автор')
                ->searchable(),
            
            Tables\Columns\TextColumn::make('content')
                ->label('Комментарий')
                ->limit(50),
            
            Tables\Columns\TextColumn::make('commentable_type')
                ->label('Тип')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'App\Models\Chapter' => 'Глава',
                    'App\Models\Novel' => 'Новелла',
                    'App\Models\User' => 'Профиль',
                    default => 'Неизвестно',
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'App\Models\Chapter' => 'info',
                    'App\Models\Novel' => 'success',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime('d.m.Y H:i')
                ->label('Дата'),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
