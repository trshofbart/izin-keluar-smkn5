<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Kelas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jurusan_id')
                ->relationship('jurusan', 'nama')
                ->required()
                ->searchable()
                ->preload()
                ->label('Jurusan'),
            Forms\Components\Select::make('tingkat')
                ->options(['X' => 'X', 'XI' => 'XI', 'XII' => 'XII', 'XIII' => 'XIII'])
                ->required(),
            Forms\Components\TextInput::make('nama')
                ->required()
                ->maxLength(255)
                ->placeholder('XII RPL A'),
            // Mapping wali kelas
            Forms\Components\Select::make('wali_kelas_id')
                ->relationship('waliKelas', 'nama')
                ->searchable()
                ->preload()
                ->label('Wali Kelas'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tingkat')->sortable(),
                Tables\Columns\TextColumn::make('jurusan.nama')->label('Jurusan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('waliKelas.nama')->label('Wali Kelas')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jurusan_id')->relationship('jurusan', 'nama')->label('Jurusan'),
                Tables\Filters\SelectFilter::make('tingkat')->options(['X' => 'X', 'XI' => 'XI', 'XII' => 'XII', 'XIII' => 'XIII']),
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
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
