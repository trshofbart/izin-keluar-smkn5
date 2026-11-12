<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_izin', function (Blueprint $table) {
            // Cara Keluar tidak lagi wajib diisi siswa.
            $table->enum('cara_pulang', ['dijemput', 'pulang_sendiri'])->nullable()->change();
            // Waktu rekap dikirim ke pos: setelah ini izin dinonaktifkan
            // (tidak bisa diakses / dipakai lagi oleh siswa).
            $table->timestamp('dipakai_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_izin', function (Blueprint $table) {
            $table->dropColumn('dipakai_pada');
            $table->enum('cara_pulang', ['dijemput', 'pulang_sendiri'])->nullable(false)->change();
        });
    }
};
