<?php

namespace App\Filament\Widgets;

use App\Models\PermohonanIzin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IzinStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $hariIni = PermohonanIzin::whereDate('tanggal', today())->count();
        $menunggu = PermohonanIzin::where('status', 'menunggu')->count();
        $disetujui = PermohonanIzin::where('status', 'disetujui_semua')->count();
        $total = PermohonanIzin::count();

        return [
            Stat::make('Permohonan Hari Ini', $hariIni)
                ->description('Dibuat hari ini, '.today()->format('d-m-Y'))
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),
            Stat::make('Menunggu Persetujuan', $menunggu)
                ->description('Belum semua guru menyetujui')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Disetujui (Diizinkan Keluar)', $disetujui)
                ->description('Semua guru sudah menyetujui')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Total Permohonan', $total)
                ->description('Semua waktu')
                ->icon('heroicon-o-qr-code')
                ->color('info'),
        ];
    }
}
