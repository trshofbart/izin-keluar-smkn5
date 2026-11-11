<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan_izin')->cascadeOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
            $table->foreignId('guru_id')->constrained('guru');
            $table->enum('peran', ['guru_mapel', 'wali_kelas'])->default('guru_mapel');
            $table->enum('status', ['menunggu', 'disetujui'])->default('menunggu');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_item');
    }
};
