<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = ['nama', 'nip', 'no_hp'];

    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function guruMapel(): HasMany
    {
        return $this->hasMany(GuruMapel::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(GuruToken::class);
    }

    public function tokenAktif(): ?GuruToken
    {
        return $this->tokens()->where('aktif', true)->latest()->first();
    }

    public function permohonanItems(): HasMany
    {
        return $this->hasMany(PermohonanItem::class);
    }
}
