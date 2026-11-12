<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * QR pos statis + kiriman rekap + role satpam (2026-11-12, permintaan Bart):
     * - pos_qr: QR statis yang dibuat admin lalu dicetak (mis. di pos satpam).
     * - pos_kirim: catatan rekap izin yang dikirim siswa ke sebuah QR pos.
     * - users.role: 'admin' (panel Filament) atau 'satpam' (halaman satpam).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('email');
        });

        Schema::create('pos_qr', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 32)->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pos_kirim', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_qr_id')->constrained('pos_qr')->cascadeOnDelete();
            $table->foreignId('permohonan_id')->constrained('permohonan_izin')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['pos_qr_id', 'permohonan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_kirim');
        Schema::dropIfExists('pos_qr');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
