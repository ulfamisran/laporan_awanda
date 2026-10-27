<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_insentifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->string('judul_surat');
            $table->string('periode_label');
            $table->string('program')->default('Makan Bergizi Gratis');
            $table->string('nama_sppg');
            $table->string('nama_peminta');
            $table->string('jabatan_peminta');
            $table->date('tanggal_perihal');
            $table->decimal('jumlah', 15, 2);
            $table->text('perihal_teks')->nullable();
            $table->text('catatan')->nullable();
            $table->string('tempat_ttd')->nullable();
            $table->date('tanggal_ttd')->nullable();
            $table->string('ttd_mengetahui_jabatan')->nullable();
            $table->string('ttd_mengetahui_nama')->nullable();
            $table->string('ttd_menyetujui_jabatan')->nullable();
            $table->string('ttd_menyetujui_nama')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_insentifs');
    }
};
