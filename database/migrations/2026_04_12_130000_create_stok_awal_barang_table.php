<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_awal_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['barang_id', 'profil_mbg_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_awal_barang');
    }
};
