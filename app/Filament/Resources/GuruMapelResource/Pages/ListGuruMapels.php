<?php

namespace App\Filament\Resources\GuruMapelResource\Pages;

use App\Filament\Resources\GuruMapelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuruMapels extends ListRecords
{
    protected static string $resource = GuruMapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
