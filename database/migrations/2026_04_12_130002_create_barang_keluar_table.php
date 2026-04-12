<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('satuan', 32);
            $table->string('tujuan_penggunaan', 32);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};
