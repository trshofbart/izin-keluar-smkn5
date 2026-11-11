<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GuruToken extends Model
{
    protected $table = 'guru_token';

    protected $fillable = ['guru_id', 'token', 'aktif', 'dibuat_oleh'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public static function generateToken(): string
    {
        return Str::upper(Str::random(6));
    }

    /** Token ditampilkan sebagian saja di halaman siswa (mask). */
    public function tokenMasked(): string
    {
        return substr($this->token, 0, 2).'****';
    }
}
