<?php

namespace App\Filament\Resources\PermohonanIzinResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Guru yang Dimintai Izin';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('guru_id')->relationship('guru', 'nama')->required()->searchable()->preload()->label('Guru'),
            Forms\Components\Select::make('mapel_id')->relationship('mapel', 'nama')->searchable()->preload()->label('Mata Pelajaran'),
            Forms\Components\Select::make('peran')
                ->options(['guru_mapel' => 'Guru Mapel', 'wali_kelas' => 'Wali Kelas'])
                ->required(),
            Forms\Components\Select::make('status')
                ->options(['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui'])
                ->required(),
            Forms\Components\Textarea::make('catatan')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('guru.nama')
            ->columns([
                Tables\Columns\TextColumn::make('guru.nama')->label('Guru')->searchable(),
                Tables\Columns\TextColumn::make('mapel.nama')->label('Mata Pelajaran')->placeholder('— (Wali Kelas)'),
                Tables\Columns\TextColumn::make('peran')->badge()
                    ->colors(['info' => 'guru_mapel', 'success' => 'wali_kelas'])
                    ->formatStateUsing(fn (string $state) => $state === 'wali_kelas' ? 'Wali Kelas' : 'Guru Mapel'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->colors(['warning' => 'menunggu', 'success' => 'disetujui'])
                    ->formatStateUsing(fn (string $state) => $state === 'disetujui' ? 'Disetujui' : 'Menunggu'),
                Tables\Columns\TextColumn::make('disetujui_pada')->dateTime()->label('Disetujui Pada')->placeholder('—'),
                Tables\Columns\TextColumn::make('persetujuan.token_digunakan')->label('Token (mask)')
                    ->formatStateUsing(fn ($state) => $state ? substr($state, 0, 2).'****' : '—'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
