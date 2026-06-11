<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ChapterErrorResource\Pages;
use App\Models\ChapterError;
use App\Models\User;
use App\Notifications\ChapterErrorResolved;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;

class ChapterErrorResource extends Resource {
    protected static ?string $model            = ChapterError::class;
    protected static ?string $navigationIcon   = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel  = 'Ошибки в тексте';
    protected static ?string $navigationGroup  = 'Контент';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Ошибка в тексте';
    protected static ?string $pluralModelLabel = 'Ошибки в тексте';

    public static function getNavigationBadge(): ?string {
        $count = ChapterError::where('status', 'new')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string {
        return 'warning';
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Репортер')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (ChapterError $r) => $r->user_id
                        ? route('filament.admin.resources.users.edit', $r->user_id) : null, true),

                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('Глава')
                    ->searchable()
                    ->limit(35)
                    ->url(fn (ChapterError $r) => $r->chapter
                        ? route('novel.read', [$r->chapter->novel_id, $r->chapter_id]) : null, true),

                Tables\Columns\TextColumn::make('selected_text')
                    ->label('Фрагмент')
                    ->limit(55)
                    ->tooltip(fn (ChapterError $r) => $r->selected_text)
                    ->wrap(),

                Tables\Columns\TextColumn::make('suggestion')
                    ->label('Исправление')
                    ->limit(45)
                    ->placeholder('—')
                    ->tooltip(fn (ChapterError $r) => $r->suggestion),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'new'      => 'warning',
                        'reviewed' => 'info',
                        'fixed'    => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new'      => 'Новая',
                        'reviewed' => 'Проверена',
                        'fixed'    => 'Исправлена',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new'      => 'Новые',
                        'reviewed' => 'Проверены',
                        'fixed'    => 'Исправлены',
                    ]),
            ])
            ->actions([
                Action::make('review')
                    ->label('Проверено')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (ChapterError $r) => $r->status === 'new')
                    ->action(function (ChapterError $r) {
                        $r->update(['status' => 'reviewed']);
                        if ($r->user_id) {
                            optional(User::find($r->user_id))->notify(new ChapterErrorResolved($r));
                        }
                        Notification::make()->title('Статус обновлён')->success()->send();
                    }),

                Action::make('fix')
                    ->label('Исправлено')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ChapterError $r) => $r->status !== 'fixed')
                    ->action(function (ChapterError $r) {
                        $r->update(['status' => 'fixed']);
                        if ($r->user_id) {
                            optional(User::find($r->user_id))->notify(new ChapterErrorResolved($r));
                        }
                        Notification::make()->title('Ошибка отмечена как исправленная')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function canCreate(): bool { return false; }

    public static function getRelations(): array { return []; }

    public static function getPages(): array {
        return [
            'index' => Pages\ListChapterErrors::route('/'),
        ];
    }
}
