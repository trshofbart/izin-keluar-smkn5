<?php

namespace App\Filament\Resources\PosQrResource\Pages;

use App\Filament\Resources\PosQrResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosQr extends EditRecord
{
    protected static string $resource = PosQrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
