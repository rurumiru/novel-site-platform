<?php
namespace App\Filament\Resources\TranslationTeamResource\Pages;
use App\Filament\Resources\TranslationTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTranslationTeams extends ListRecords {
    protected static string $resource = TranslationTeamResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()->label('Создать команду')];
    }
}
