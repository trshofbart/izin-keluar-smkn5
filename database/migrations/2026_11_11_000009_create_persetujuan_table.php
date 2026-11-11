<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persetujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_item_id')->constrained('permohonan_item')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru');
            $table->string('token_digunakan', 10);
            $table->timestamp('disetujui_pada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan');
    }
};
