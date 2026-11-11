<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruMapelResource\Pages;
use App\Models\GuruMapel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuruMapelResource extends Resource
{
    protected static ?string $model = GuruMapel::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Mapping Guru & Mapel';

    protected static ?string $modelLabel = 'Mapping Guru & Mapel';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('guru_id')
                ->relationship('guru', 'nama')
                ->required()
                ->searchable()
                ->preload()
                ->label('Guru'),
            Forms\Components\Select::make('mapel_id')
                ->relationship('mapel', 'nama')
                ->required()
                ->searchable()
                ->preload()
                ->label('Mata Pelajaran'),
            Forms\Components\Select::make('kelas_id')
                ->relationship('kelas', 'nama')
                ->searchable()
                ->preload()
                ->label('Kelas (opsional)'),
            Forms\Components\Select::make('jurusan_id')
                ->relationship('jurusan', 'nama')
                ->searchable()
                ->preload()
                ->label('Jurusan (opsional)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guru.nama')->label('Guru')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama')->label('Mata Pelajaran')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kelas.nama')->label('Kelas')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('jurusan.nama')->label('Jurusan')->searchable()->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guru_id')->relationship('guru', 'nama')->label('Guru'),
                Tables\Filters\SelectFilter::make('mapel_id')->relationship('mapel', 'nama')->label('Mata Pelajaran'),
                Tables\Filters\SelectFilter::make('jurusan_id')->relationship('jurusan', 'nama')->label('Jurusan'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuruMapels::route('/'),
            'create' => Pages\CreateGuruMapel::route('/create'),
            'edit' => Pages\EditGuruMapel::route('/{record}/edit'),
        ];
    }
}
