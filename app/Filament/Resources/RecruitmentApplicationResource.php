<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecruitmentApplicationResource\Pages;
use App\Models\RecruitmentApplication;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Schema;

class RecruitmentApplicationResource extends Resource
{
    protected static ?string $model = RecruitmentApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Отклики на набор';
    protected static ?string $navigationGroup = 'Модерация';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel = 'Отклик';
    protected static ?string $pluralModelLabel = 'Отклики';

    public static function canAccess(): bool
    {
        return Schema::hasTable('recruitment_applications');
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')->label('Вакансия')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('contact')->label('Контакты')->limit(20),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'new' => 'info',
                    'viewed' => 'warning',
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('post.title')->label('Вакансия'),
            TextEntry::make('name')->label('Имя'),
            TextEntry::make('email')->label('Email'),
            TextEntry::make('contact')->label('Контакты'),
            TextEntry::make('experience')->label('Опыт')->markdown()->columnSpanFull(),
            TextEntry::make('skills')->label('Навыки')->markdown()->columnSpanFull(),
            TextEntry::make('motivation')->label('Мотивация')->markdown()->columnSpanFull(),
            TextEntry::make('status')->label('Статус')->badge(),
            TextEntry::make('created_at')->label('Дата отклика')->dateTime('d.m.Y H:i'),
            TextEntry::make('user.name')->label('Пользователь')->default('Гость'),
        ])->columns(2);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecruitmentApplications::route('/'),
            'view' => Pages\ViewRecruitmentApplication::route('/{record}'),
        ];
    }
}
