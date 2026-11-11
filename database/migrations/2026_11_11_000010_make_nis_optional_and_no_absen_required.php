<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NIS jadi opsional, No. Absen jadi wajib (permintaan 2026-11-11).
 *
 * Untuk database yang sudah jalan (Laragon): jalankan `php artisan migrate`
 * setelah `git pull`. Migrasi ini mengubah kolom yang sudah ada; instalasi
 * baru tidak butuh migrasi ini karena migrasi create sudah diperbarui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_izin', function (Blueprint $table) {
            $table->string('nis', 30)->nullable()->change();
            $table->string('no_absen', 10)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_izin', function (Blueprint $table) {
            $table->string('nis', 30)->nullable(false)->change();
            $table->string('no_absen', 10)->nullable()->change();
        });
    }
};
