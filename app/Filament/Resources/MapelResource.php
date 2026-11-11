<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MapelResource\Pages;
use App\Models\Mapel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MapelResource extends Resource
{
    protected static ?string $model = Mapel::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Mata Pelajaran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('kode')->maxLength(20),
            Forms\Components\Select::make('kelompok')
                ->options(['A' => 'A', 'B' => 'B', 'C1' => 'C1', 'C2' => 'C2', 'C3' => 'C3'])
                ->label('Kelompok'),
            Forms\Components\Select::make('jurusan_id')
                ->relationship('jurusan', 'nama')
                ->searchable()
                ->preload()
                ->label('Jurusan (kosongkan bila mapel umum)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kode')->searchable(),
                Tables\Columns\TextColumn::make('kelompok')->sortable(),
                Tables\Columns\TextColumn::make('jurusan.nama')->label('Jurusan')->searchable()->sortable()
                    ->placeholder('Umum'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kelompok')->options(['A' => 'A', 'B' => 'B', 'C1' => 'C1', 'C2' => 'C2', 'C3' => 'C3']),
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
            'index' => Pages\ListMapels::route('/'),
            'create' => Pages\CreateMapel::route('/create'),
            'edit' => Pages\EditMapel::route('/{record}/edit'),
        ];
    }
}
