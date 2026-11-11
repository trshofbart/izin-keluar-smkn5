<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Persetujuan extends Model
{
    protected $table = 'persetujuan';

    protected $fillable = ['permohonan_item_id', 'guru_id', 'token_digunakan', 'disetujui_pada'];

    protected $casts = [
        'disetujui_pada' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PermohonanItem::class, 'permohonan_item_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /** Token untuk rekap internal admin; di halaman siswa tampilkan mask. */
    public function tokenMasked(): string
    {
        return substr($this->token_digunakan, 0, 2).'****';
    }
}
