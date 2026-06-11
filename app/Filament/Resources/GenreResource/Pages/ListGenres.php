<?php

namespace App\Filament\Resources\GenreResource\Pages;

use App\Filament\Resources\GenreResource;
use App\Models\Genre;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListGenres extends ListRecords
{
    protected static string $resource = GenreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Новый жанр'),

            Actions\Action::make('export_all')
                ->label('Экспорт CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $records = Genre::query()->orderBy('name')->get();
                    return GenreResource::streamCsv($records, 'genres-all-' . date('Ymd-His') . '.csv');
                }),

            Actions\Action::make('import_csv')
                ->label('Импорт CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('CSV-файл')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('tmp/genres-import')
                        ->helperText('Заголовки: name, slug, description, color, icon, is_adult, is_visible, sort_order. Разделитель — , или ;'),
                ])
                ->action(function (array $data) {
                    $relative = $data['file'] ?? null;
                    if (!$relative) {
                        Notification::make()->title('Файл не получен')->danger()->send();
                        return;
                    }
                    $abs = Storage::disk('local')->path($relative);
                    $result = GenreResource::importCsv($abs);
                    @unlink($abs);

                    $msg = "Создано: {$result['created']}, обновлено: {$result['updated']}";
                    if (!empty($result['errors'])) {
                        $msg .= ". Ошибок: " . count($result['errors']);
                    }
                    Notification::make()
                        ->title('Импорт завершён')
                        ->body($msg)
                        ->success()
                        ->send();
                }),
        ];
    }
}
