<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_barang_items')) {
            return;
        }

        Schema::create('order_barang_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_barang_id')->constrained('order_barang')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->cascadeOnUpdate()->nullOnDelete();
            $table->string('nama_barang');
            $table->decimal('harga_barang', 15, 2)->default(0);
            $table->decimal('jumlah_barang', 15, 2)->default(0);
            $table->string('satuan_barang', 32);
            $table->unsignedSmallInteger('jumlah_hari_pemakaian')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_barang_items');
    }
};
