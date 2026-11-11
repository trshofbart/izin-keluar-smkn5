<?php

namespace App\Filament\Resources\PermohonanIzinResource\Pages;

use App\Filament\Resources\PermohonanIzinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermohonanIzins extends ListRecords
{
    protected static string $resource = PermohonanIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
