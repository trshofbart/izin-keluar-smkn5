<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PermohonanIzin extends Model
{
    protected $table = 'permohonan_izin';

    protected $fillable = [
        'kode', 'nama_siswa', 'nis', 'no_absen', 'kelas_id', 'jurusan_id',
        'keperluan', 'tanggal', 'jam_ke_mulai', 'jam_ke_selesai',
        'kendaraan', 'cara_pulang', 'nama_penjemput', 'status', 'barcode_kode',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_ke_mulai' => 'integer',
        'jam_ke_selesai' => 'integer',
    ];

    public static function generateKode(): string
    {
        do {
            $kode = 'IZN-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    public static function generateBarcodeKode(): string
    {
        do {
            $kode = Str::lower(Str::random(32));
        } while (static::where('barcode_kode', $kode)->exists());

        return $kode;
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PermohonanItem::class, 'permohonan_id');
    }

    public function isDisetujuiSemua(): bool
    {
        return $this->status === 'disetujui_semua';
    }

    /** Hitung ulang status dari item-itemnya. */
    public function refreshStatus(): void
    {
        $total = $this->items()->count();
        $disetujui = $this->items()->where('status', 'disetujui')->count();

        $this->status = match (true) {
            $total > 0 && $disetujui === $total => 'disetujui_semua',
            $disetujui > 0 => 'disetujui_sebagian',
            default => 'menunggu',
        };
        $this->save();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu Persetujuan',
            'disetujui_sebagian' => 'Disetujui Sebagian',
            'disetujui_semua' => 'Disetujui Semua — Diizinkan Keluar',
            default => $this->status,
        };
    }
}
