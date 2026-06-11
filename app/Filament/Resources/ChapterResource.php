<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ChapterResource\Pages;
use App\Models\Chapter;
use Filament\Resources\Resource;

class ChapterResource extends Resource {
    protected static ?string $model = Chapter::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function getPages(): array {
        return [
            'index' => Pages\ListChapters::route('/'),
            'create' => Pages\CreateChapter::route('/create'),
            'edit' => Pages\EditChapter::route('/{record}/edit'),
        ];
    }
}
