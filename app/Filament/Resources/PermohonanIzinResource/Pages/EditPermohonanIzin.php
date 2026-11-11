<?php

namespace App\Filament\Resources\PermohonanIzinResource\Pages;

use App\Filament\Resources\PermohonanIzinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermohonanIzin extends EditRecord
{
    protected static string $resource = PermohonanIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
