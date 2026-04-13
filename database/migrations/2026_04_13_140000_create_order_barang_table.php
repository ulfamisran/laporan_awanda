<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_barang')) {
            return;
        }

        Schema::create('order_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_order')->unique();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_order');
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_barang');
    }
};
