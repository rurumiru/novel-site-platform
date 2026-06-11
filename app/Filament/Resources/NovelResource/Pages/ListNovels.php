<?php
namespace App\Filament\Resources\NovelResource\Pages;
use App\Filament\Resources\NovelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNovels extends ListRecords {
    protected static string $resource = NovelResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
