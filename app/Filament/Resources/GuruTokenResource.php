<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruTokenResource\Pages;
use App\Models\Guru;
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
            ->headerActions([
                // Membuat token untuk SEMUA guru yang belum punya token.
                // Token guru yang sudah ada tidak diubah (pakai Generate Ulang
                // per guru atau Ubah Token Serentak untuk itu).
                Tables\Actions\Action::make('generateSemua')
                    ->label('Generate Token Semua Guru')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->action(function () {
                        $sudahAda = GuruToken::pluck('guru_id')->all();
                        $belumPunya = Guru::whereNotIn('id', $sudahAda)->get();

                        foreach ($belumPunya as $guru) {
                            GuruToken::create([
                                'guru_id' => $guru->id,
                                'token' => GuruToken::generateToken(),
                                'aktif' => true,
                                'dibuat_oleh' => auth()->id(),
                            ]);
                        }

                        if ($belumPunya->isEmpty()) {
                            Notification::make()
                                ->title('Semua guru sudah memiliki token. Tidak ada token baru yang dibuat.')
                                ->info()->send();
                        } else {
                            Notification::make()
                                ->title($belumPunya->count().' token dibuat untuk guru yang belum punya token.')
                                ->success()->send();
                        }
                    }),
                // Mengganti SEMUA token yang ada sekaligus dengan token yang
                // diisi admin — semua guru memakai token baru yang sama.
                Tables\Actions\Action::make('ubahSerentak')
                    ->label('Ubah Token Serentak')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->modalHeading('Ubah token semua guru sekaligus')
                    ->modalDescription('Isi token baru di bawah ini — SEMUA guru akan memakai token yang sama. Token lama berhenti berlaku segera setelah diubah.')
                    ->modalSubmitActionLabel('Ubah Semua Token')
                    ->form([
                        Forms\Components\TextInput::make('token_baru')
                            ->label('Token Baru untuk Semua Guru')
                            ->required()
                            ->minLength(6)
                            ->maxLength(10)
                            ->alphaNum()
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->helperText('6–10 karakter huruf/angka. Semua guru memakai token yang sama ini.')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('acak')
                                    ->icon('heroicon-o-arrow-path')
                                    ->label('Acak')
                                    ->action(fn (Set $set) => $set('token_baru', GuruToken::generateToken()))
                            ),
                    ])
                    ->action(function (array $data) {
                        $tokenBaru = Str::upper(trim($data['token_baru']));

                        $tokens = GuruToken::all();
                        foreach ($tokens as $token) {
                            $token->update(['token' => $tokenBaru]);
                        }

                        Notification::make()
                            ->title($tokens->count().' token guru berhasil diubah serentak menjadi '.$tokenBaru.'.')
                            ->success()->send();
                    }),
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
