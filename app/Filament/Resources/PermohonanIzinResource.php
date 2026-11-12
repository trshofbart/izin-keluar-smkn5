<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermohonanIzinResource\Pages;
use App\Filament\Resources\PermohonanIzinResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\PermohonanIzinResource\RelationManagers\SiswasRelationManager;
use App\Models\PermohonanIzin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermohonanIzinResource extends Resource
{
    protected static ?string $model = PermohonanIzin::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Permohonan Izin';

    protected static ?string $modelLabel = 'Permohonan Izin';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode')->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('nama_siswa')->required()->maxLength(100),
            Forms\Components\TextInput::make('nis')->maxLength(30)->label('NIS (opsional)'),
            Forms\Components\TextInput::make('no_absen')->required()->maxLength(10)->label('No. Absen'),
            Forms\Components\Select::make('kelas_id')->relationship('kelas', 'nama')->required()->searchable()->preload()->label('Kelas'),
            Forms\Components\Select::make('jurusan_id')->relationship('jurusan', 'nama')->required()->searchable()->preload()->label('Jurusan'),
            Forms\Components\Textarea::make('keperluan')->required()->columnSpanFull(),
            Forms\Components\DatePicker::make('tanggal')->required()->default(now()),
            Forms\Components\TextInput::make('jam_ke_mulai')->numeric()->minValue(1)->maxValue(10)->required()->label('Jam Ke (mulai)'),
            Forms\Components\TextInput::make('jam_ke_selesai')->numeric()->minValue(1)->maxValue(10)->required()->label('Jam Ke (selesai)'),
            Forms\Components\Select::make('kendaraan')
                ->options([
                    'Sepeda Motor' => 'Sepeda Motor', 'Mobil' => 'Mobil', 'Sepeda' => 'Sepeda',
                    'Jalan Kaki' => 'Jalan Kaki', 'Antar Jemput' => 'Antar Jemput', 'Lainnya' => 'Lainnya',
                ])->required(),
            Forms\Components\Select::make('cara_pulang')
                ->options(['dijemput' => 'Dijemput', 'pulang_sendiri' => 'Pulang Sendiri'])
                ->required()->label('Cara Keluar'),
            Forms\Components\TextInput::make('nama_penjemput')->maxLength(100)->label('Nama Penjemput'),
            Forms\Components\Select::make('status')
                ->options(['menunggu' => 'Menunggu', 'disetujui_sebagian' => 'Disetujui Sebagian', 'disetujui_semua' => 'Disetujui Semua'])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable()->copyable()->label('Kode'),
                Tables\Columns\TextColumn::make('nama_siswa')->label('Siswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('siswas_count')->counts('siswas')->label('Jml Siswa'),
                Tables\Columns\TextColumn::make('nis')->label('NIS')->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama')->label('Kelas')->sortable(),
                Tables\Columns\TextColumn::make('jurusan.nama')->label('Jurusan')->sortable(),
                Tables\Columns\TextColumn::make('keperluan')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('jam_ke_mulai')->label('Jam')->formatStateUsing(fn ($record) => "Jam ke-{$record->jam_ke_mulai} s.d. {$record->jam_ke_selesai}"),
                Tables\Columns\TextColumn::make('kendaraan')->sortable(),
                Tables\Columns\TextColumn::make('cara_pulang')->label('Cara Keluar')
                    ->formatStateUsing(fn (string $state) => $state === 'dijemput' ? 'Dijemput' : 'Pulang Sendiri'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->colors([
                        'warning' => 'menunggu',
                        'info' => 'disetujui_sebagian',
                        'success' => 'disetujui_semua',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'menunggu' => 'Menunggu',
                        'disetujui_sebagian' => 'Disetujui Sebagian',
                        'disetujui_semua' => 'Disetujui Semua',
                    }),
                Tables\Columns\TextColumn::make('tanggal')->date()->sortable()->label('Tanggal'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'menunggu' => 'Menunggu',
                    'disetujui_sebagian' => 'Disetujui Sebagian',
                    'disetujui_semua' => 'Disetujui Semua',
                ]),
                Tables\Filters\SelectFilter::make('kelas_id')->relationship('kelas', 'nama')->label('Kelas'),
                Tables\Filters\SelectFilter::make('jurusan_id')->relationship('jurusan', 'nama')->label('Jurusan'),
                Tables\Filters\Filter::make('tanggal')->form([
                    Forms\Components\DatePicker::make('dari')->label('Dari tanggal'),
                    Forms\Components\DatePicker::make('sampai')->label('Sampai tanggal'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['dari'] ?? null, fn ($q, $d) => $q->whereDate('tanggal', '>=', $d))
                        ->when($data['sampai'] ?? null, fn ($q, $d) => $q->whereDate('tanggal', '<=', $d));
                }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SiswasRelationManager::class,
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermohonanIzins::route('/'),
            'create' => Pages\CreatePermohonanIzin::route('/create'),
            'view' => Pages\ViewPermohonanIzin::route('/{record}'),
            'edit' => Pages\EditPermohonanIzin::route('/{record}/edit'),
        ];
    }
}
