<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->foreignId('kategori_barang_id')->constrained('kategori_barang')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('satuan', 32);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('stok_minimum', 15, 4)->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 16)->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
