<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosQrResource\Pages;
use App\Models\PosQr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PosQrResource extends Resource
{
    protected static ?string $model = PosQr::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'QR Pos Statis';

    protected static ?string $modelLabel = 'QR Pos Statis';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')->required()->maxLength(100)
                ->label('Nama Pos')
                ->helperText('Contoh: Pos Satpam Gerbang Utama. Nama ini tampil di kertas QR yang dicetak.'),
            Forms\Components\TextInput::make('kode')->disabled()->dehydrated(false)
                ->label('Kode QR')
                ->helperText('Dibuat otomatis sekali saat pos ditambahkan dan tidak berubah, supaya QR yang sudah dicetak tetap berlaku.'),
            Forms\Components\Toggle::make('aktif')->default(true)
                ->helperText('Pos nonaktif tidak menerima kiriman rekap baru.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama Pos')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kode')->label('Kode')->copyable()->limit(16),
                Tables\Columns\TextColumn::make('kirims_count')->counts('kirims')->label('Rekap Terkirim'),
                Tables\Columns\IconColumn::make('aktif')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Dibuat'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak QR')
                    ->icon('heroicon-o-printer')
                    ->url(fn (PosQr $record) => route('pos.cetak', $record->kode))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosQrs::route('/'),
            'create' => Pages\CreatePosQr::route('/create'),
            'view' => Pages\ViewPosQr::route('/{record}'),
            'edit' => Pages\EditPosQr::route('/{record}/edit'),
        ];
    }
}
