<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_limbah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laporan', 32)->unique();
            $table->foreignId('kategori_limbah_id')->constrained('kategori_limbah')->restrictOnDelete();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->restrictOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 10, 2);
            $table->string('satuan', 20);
            $table->string('jenis_penanganan', 30);
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('gambar')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['profil_mbg_id', 'tanggal']);
            $table->index(['kategori_limbah_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_limbah');
    }
};
