<?php
namespace App\Filament\Resources\StickerResource\Pages;

use App\Filament\Resources\StickerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSticker extends CreateRecord
{
    protected static string $resource = StickerResource::class;

    protected function afterCreate(): void
    {
        \Illuminate\Support\Facades\Cache::forget('eri-stickers-map');
    }
}
