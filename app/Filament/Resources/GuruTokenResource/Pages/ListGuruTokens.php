<?php

namespace App\Filament\Resources\GuruTokenResource\Pages;

use App\Filament\Resources\GuruTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuruTokens extends ListRecords
{
    protected static string $resource = GuruTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
