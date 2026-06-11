<?php

namespace App\Filament\Resources\PlusPlanResource\Pages;

use App\Filament\Resources\PlusPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlusPlan extends EditRecord {
    protected static string $resource = PlusPlanResource::class;

    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
