<?php
namespace App\Filament\Resources\TranslationTeamResource\Pages;
use App\Filament\Resources\TranslationTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTranslationTeam extends EditRecord {
    protected static string $resource = TranslationTeamResource::class;
    protected function getHeaderActions(): array {
        return [
            Actions\Action::make('view_site')
                ->label('На сайте')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn() => route('teams.show', $this->record->slug))->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
