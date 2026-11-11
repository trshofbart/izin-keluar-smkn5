<?php

namespace App\Filament\Resources\GuruTokenResource\Pages;

use App\Filament\Resources\GuruTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuruToken extends EditRecord
{
    protected static string $resource = GuruTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
