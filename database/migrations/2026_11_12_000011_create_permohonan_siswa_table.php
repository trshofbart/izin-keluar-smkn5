<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu permohonan bisa berisi banyak siswa (2026-11-12, permintaan Bart):
     * kolom nama, NIS, no absen, kelas, jurusan di form bisa ditambah.
     * Kolom siswa di permohonan_izin tetap diisi siswa pertama (rekap lama).
     */
    public function up(): void
    {
        Schema::create('permohonan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan_izin')->cascadeOnDelete();
            $table->string('nama_siswa', 100);
            $table->string('nis', 30)->nullable();
            $table->string('no_absen', 10);
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('jurusan_id')->constrained('jurusan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_siswa');
    }
};
