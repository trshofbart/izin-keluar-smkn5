<?php

namespace App\Filament\Resources\PermohonanIzinResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'siswas';

    protected static ?string $title = 'Siswa dalam Permohonan';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_siswa')->required()->maxLength(100)->label('Nama Siswa'),
            Forms\Components\TextInput::make('nis')->maxLength(30)->label('NIS (opsional)'),
            Forms\Components\TextInput::make('no_absen')->required()->maxLength(10)->label('No. Absen'),
            Forms\Components\Select::make('kelas_id')->relationship('kelas', 'nama')->required()->searchable()->preload()->label('Kelas')
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, $state) {
                    $kelas = \App\Models\Kelas::find($state);
                    $set('jurusan_id', $kelas?->jurusan_id);
                }),
            Forms\Components\Select::make('jurusan_id')->relationship('jurusan', 'nama')->required()->searchable()->preload()->label('Jurusan'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_siswa')
            ->columns([
                Tables\Columns\TextColumn::make('nama_siswa')->label('Nama Siswa')->searchable(),
                Tables\Columns\TextColumn::make('nis')->label('NIS')->placeholder('—'),
                Tables\Columns\TextColumn::make('no_absen')->label('No. Absen'),
                Tables\Columns\TextColumn::make('kelas.nama')->label('Kelas'),
                Tables\Columns\TextColumn::make('jurusan.nama')->label('Jurusan'),
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
