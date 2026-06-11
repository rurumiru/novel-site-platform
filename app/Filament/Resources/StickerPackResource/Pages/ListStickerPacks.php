<?php
namespace App\Filament\Resources\StickerPackResource\Pages;

use App\Filament\Resources\StickerPackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStickerPacks extends ListRecords
{
    protected static string $resource = StickerPackResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
