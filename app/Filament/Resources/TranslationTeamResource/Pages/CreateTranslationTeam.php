<?php
namespace App\Filament\Resources\TranslationTeamResource\Pages;
use App\Filament\Resources\TranslationTeamResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTranslationTeam extends CreateRecord {
    protected static string $resource = TranslationTeamResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name'] ?? '');
        }
        return $data;
    }
    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
