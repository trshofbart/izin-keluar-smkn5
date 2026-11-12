<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PosQr extends Model
{
    protected $table = 'pos_qr';

    protected $fillable = [
        'nama', 'kode', 'aktif',
    ];

    protected $attributes = [
        'aktif' => true,
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Kode QR statis dibuat otomatis sekali dan tidak berubah (untuk dicetak).
        static::creating(function (PosQr $pos) {
            if (empty($pos->kode)) {
                $pos->kode = static::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $kode = Str::lower(Str::random(24));
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    /** URL yang dituju QR statis ini. */
    public function url(): string
    {
        return route('pos.show', $this->kode);
    }

    public function kirims(): HasMany
    {
        return $this->hasMany(PosKirim::class, 'pos_qr_id');
    }
}
