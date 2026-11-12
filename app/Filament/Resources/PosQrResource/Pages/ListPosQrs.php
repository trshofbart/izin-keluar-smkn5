<?php

namespace App\Filament\Resources\PosQrResource\Pages;

use App\Filament\Resources\PosQrResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosQrs extends ListRecords
{
    protected static string $resource = PosQrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Generate QR Pos Baru'),
        ];
    }
}
