<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('label_ompreng_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_ompreng_id')->constrained('label_ompreng')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->string('menu_1');
            $table->string('menu_2');
            $table->string('menu_3');
            $table->string('menu_4');
            $table->string('menu_5');
            $table->decimal('energi_nilai', 10, 2)->nullable();
            $table->string('energi_satuan', 20)->nullable();
            $table->decimal('protein_nilai', 10, 2)->nullable();
            $table->string('protein_satuan', 20)->nullable();
            $table->decimal('lemak_nilai', 10, 2)->nullable();
            $table->string('lemak_satuan', 20)->nullable();
            $table->decimal('karbohidrat_nilai', 10, 2)->nullable();
            $table->string('karbohidrat_satuan', 20)->nullable();
            $table->decimal('serat_nilai', 10, 2)->nullable();
            $table->string('serat_satuan', 20)->nullable();
            $table->date('tanggal_produksi');
            $table->time('batas_konsumsi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_ompreng_items');
    }
};
