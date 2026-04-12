<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relawan_id')->constrained('relawans')->restrictOnDelete();
            $table->date('periode');
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['relawan_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};
