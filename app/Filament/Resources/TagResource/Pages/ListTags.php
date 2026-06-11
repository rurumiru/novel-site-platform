<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Новый тег'),

            Actions\Action::make('export_all')
                ->label('Экспорт CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $records = Tag::query()->orderBy('name')->get();
                    return TagResource::streamCsv($records, 'tags-all-' . date('Ymd-His') . '.csv');
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
                        ->directory('tmp/tags-import')
                        ->helperText('Заголовки: name, slug, category, description, color, icon, is_adult, is_restricted, is_visible, sort_order. Разделитель — , или ;'),
                ])
                ->action(function (array $data) {
                    $relative = $data['file'] ?? null;
                    if (!$relative) {
                        Notification::make()->title('Файл не получен')->danger()->send();
                        return;
                    }
                    $abs = Storage::disk('local')->path($relative);
                    $result = TagResource::importCsv($abs);
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
