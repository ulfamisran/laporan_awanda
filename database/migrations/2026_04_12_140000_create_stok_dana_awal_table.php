<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_dana_awal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('profil_mbg_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_dana_awal');
    }
};
