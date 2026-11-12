<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermohonanSiswa extends Model
{
    protected $table = 'permohonan_siswa';

    protected $fillable = [
        'permohonan_id', 'nama_siswa', 'nis', 'no_absen', 'kelas_id', 'jurusan_id',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanIzin::class, 'permohonan_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }
}
