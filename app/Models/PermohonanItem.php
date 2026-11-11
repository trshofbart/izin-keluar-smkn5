<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermohonanItem extends Model
{
    protected $table = 'permohonan_item';

    protected $fillable = [
        'permohonan_id', 'mapel_id', 'guru_id', 'peran', 'status', 'disetujui_pada', 'catatan',
    ];

    protected $casts = [
        'disetujui_pada' => 'datetime',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanIzin::class, 'permohonan_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function persetujuan(): HasMany
    {
        return $this->hasMany(Persetujuan::class, 'permohonan_item_id');
    }

    public function label(): string
    {
        if ($this->peran === 'wali_kelas') {
            return 'Wali Kelas';
        }

        return $this->mapel ? $this->mapel->nama : 'Guru Mapel';
    }
}
