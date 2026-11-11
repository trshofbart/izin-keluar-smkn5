<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    protected $table = 'jurusan';

    protected $fillable = ['kode', 'nama'];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function mapel(): HasMany
    {
        return $this->hasMany(Mapel::class);
    }

    public function permohonan(): HasMany
    {
        return $this->hasMany(PermohonanIzin::class);
    }
}
