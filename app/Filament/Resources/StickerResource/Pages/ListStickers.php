<?php
namespace App\Filament\Resources\StickerResource\Pages;

use App\Filament\Resources\StickerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStickers extends ListRecords
{
    protected static string $resource = StickerResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
