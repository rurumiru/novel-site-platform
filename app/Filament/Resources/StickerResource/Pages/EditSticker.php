<?php
namespace App\Filament\Resources\StickerResource\Pages;

use App\Filament\Resources\StickerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSticker extends EditRecord
{
    protected static string $resource = StickerResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        \Illuminate\Support\Facades\Cache::forget('eri-stickers-map');
    }
}
