<?php

namespace App\Filament\Resources\NovelResource\Pages;

use App\Filament\Resources\NovelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNovel extends CreateRecord
{
    protected static string $resource = NovelResource::class;
}
