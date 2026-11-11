<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruTokenResource\Pages;
use App\Models\GuruToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GuruTokenResource extends Resource
{
    protected static ?string $model = GuruToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Token Guru';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('guru_id')
                ->relationship('guru', 'nama')
                ->required()
                ->searchable()
                ->preload()
                ->label('Guru'),
            Forms\Components\TextInput::make('token')
                ->required()
                ->maxLength(10)
                ->default(fn () => Str::upper(Str::random(6)))
                ->helperText('Token 6 karakter alfanumerik. Klik tombol acak untuk membuat token baru.')
                ->suffixAction(
                    Forms\Components\Actions\Action::make('acak')
                        ->icon('heroicon-o-arrow-path')
                        ->label('Acak')
                        ->action(fn (Set $set) => $set('token', Str::upper(Str::random(6))))
                ),
            Forms\Components\Toggle::make('aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guru.nama')->label('Guru')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('token')->searchable()->copyable()->label('Token'),
                Tables\Columns\IconColumn::make('aktif')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Dibuat'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('aktif')->label('Aktif'),
                Tables\Filters\SelectFilter::make('guru_id')->relationship('guru', 'nama')->label('Guru'),
            ])
            ->actions([
                Tables\Actions\Action::make('regenerate')
                    ->label('Generate Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (GuruToken $record) {
                        $record->update(['token' => GuruToken::generateToken()]);
                        Notification::make()->title('Token baru: '.$record->token)->success()->send();
                    }),
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
            'index' => Pages\ListGuruTokens::route('/'),
            'create' => Pages\CreateGuruToken::route('/create'),
            'edit' => Pages\EditGuruToken::route('/{record}/edit'),
        ];
    }
}
