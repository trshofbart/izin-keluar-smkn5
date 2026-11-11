<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_izin', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique(); // IZN-YYYYMMDD-XXXX
            $table->string('nama_siswa');
            $table->string('nis', 30)->nullable(); // opsional sejak 2026-11-11
            $table->string('no_absen', 10); // wajib sejak 2026-11-11
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('jurusan_id')->constrained('jurusan');
            $table->text('keperluan');
            $table->date('tanggal')->default(now()->toDateString());
            $table->unsignedTinyInteger('jam_ke_mulai'); // 1-10
            $table->unsignedTinyInteger('jam_ke_selesai'); // 1-10, >= mulai
            $table->enum('kendaraan', ['Sepeda Motor', 'Mobil', 'Sepeda', 'Jalan Kaki', 'Antar Jemput', 'Lainnya']);
            $table->enum('cara_pulang', ['dijemput', 'pulang_sendiri']);
            $table->string('nama_penjemput')->nullable();
            $table->enum('status', ['menunggu', 'disetujui_sebagian', 'disetujui_semua'])->default('menunggu');
            $table->string('barcode_kode', 32)->unique(); // untuk URL QR /guru/{barcode_kode}
            $table->timestamps();

            $table->index('tanggal');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_izin');
    }
};
