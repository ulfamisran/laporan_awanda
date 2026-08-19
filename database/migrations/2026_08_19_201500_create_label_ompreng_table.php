<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('label_ompreng', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_mbg_id')->nullable()->constrained('profil_mbg')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('template_default', 32)->default('tegas');
            $table->string('lokasi_sppg');
            $table->text('peringatan');
            $table->text('himbauan');
            $table->unsignedInteger('jumlah_item')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_ompreng');
    }
};
