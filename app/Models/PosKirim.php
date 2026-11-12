<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosKirim extends Model
{
    protected $table = 'pos_kirim';

    protected $fillable = [
        'pos_qr_id', 'permohonan_id',
    ];

    public function posQr(): BelongsTo
    {
        return $this->belongsTo(PosQr::class, 'pos_qr_id');
    }

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanIzin::class, 'permohonan_id');
    }
}
