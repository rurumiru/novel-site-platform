<?php
namespace App\Filament\Resources\StickerPackResource\Pages;

use App\Filament\Resources\StickerPackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStickerPack extends EditRecord
{
    protected static string $resource = StickerPackResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
