<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dana_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 32)->unique();
            $table->foreignId('kategori_dana_keluar_id')->constrained('kategori_dana_keluar')->cascadeOnDelete();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->string('keperluan');
            $table->text('keterangan')->nullable();
            $table->json('gambar_nota')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dana_keluar');
    }
};
