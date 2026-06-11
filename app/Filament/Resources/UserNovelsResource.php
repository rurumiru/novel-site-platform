<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserNovelsResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class UserNovelsResource extends Resource {
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Новеллы по авторам';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel = 'Новеллы автора';
    protected static ?string $pluralModelLabel = 'Новеллы по авторам';
    protected static ?string $slug = 'user-novels';

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()->has('novels');
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('avatar')->disk('s3')->circular(),
            Tables\Columns\TextColumn::make('name')->label('Автор')->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('novels_count')->counts('novels')->label('Новелл')->badge(),
            Tables\Columns\TextColumn::make('total_chapters')
                ->label('Всего глав')
                ->state(fn (User $record) => $record->novels->sum(fn($n) => $n->chapters()->count())),
            Tables\Columns\TextColumn::make('total_views')
                ->label('Просмотров')
                ->state(fn (User $record) => $record->novels->sum('views')),
        ])->actions([
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Infolists\Components\Section::make('Автор')
                ->schema([
                    Infolists\Components\ImageEntry::make('avatar')->circular(),
                    Infolists\Components\TextEntry::make('name')->weight('bold'),
                    Infolists\Components\TextEntry::make('email'),
                ])->columns(3),

            Infolists\Components\Section::make('Новеллы')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('novels')
                        ->schema([
                            Infolists\Components\ImageEntry::make('cover_image')->width(80),
                            Infolists\Components\TextEntry::make('title')->label('Название')->weight('bold'),
                            Infolists\Components\TextEntry::make('status')->badge(),
                            Infolists\Components\TextEntry::make('chapters_count')
                                ->state(fn ($record) => $record->chapters()->count() . ' глав')
                                ->badge()->color('info'),
                            Infolists\Components\TextEntry::make('views')->label('Просмотры'),
                        ])
                        ->columns(5)
                ])
        ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListUserNovels::route('/'),
            'view' => Pages\ViewUserNovels::route('/{record}'),
        ];
    }
}
