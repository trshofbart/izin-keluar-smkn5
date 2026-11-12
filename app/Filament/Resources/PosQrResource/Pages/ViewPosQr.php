<?php

namespace App\Filament\Resources\PosQrResource\Pages;

use App\Filament\Resources\PosQrResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPosQr extends ViewRecord
{
    protected static string $resource = PosQrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak')
                ->label('Cetak QR')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('pos.cetak', $this->record->kode))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
