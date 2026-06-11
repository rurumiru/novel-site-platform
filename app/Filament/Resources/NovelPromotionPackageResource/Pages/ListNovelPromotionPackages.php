<?php
namespace App\Filament\Resources\NovelPromotionPackageResource\Pages;

use App\Filament\Resources\NovelPromotionPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNovelPromotionPackages extends ListRecords {
    protected static string $resource = NovelPromotionPackageResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()];
    }
}
