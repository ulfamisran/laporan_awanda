<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_barang')) {
            return;
        }

        Schema::table('order_barang', function (Blueprint $table) {
            // Lepas unique global agar nomor bisa sama di periode berbeda.
            try {
                $table->dropUnique(['nomor_order']);
            } catch (\Throwable) {
                // Index name may differ across drivers; ignore if already dropped.
            }
        });

        Schema::table('order_barang', function (Blueprint $table) {
            $table->unique(['profil_mbg_id', 'periode_id', 'nomor_order'], 'order_barang_profil_periode_nomor_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_barang')) {
            return;
        }

        Schema::table('order_barang', function (Blueprint $table) {
            $table->dropUnique('order_barang_profil_periode_nomor_unique');
            $table->unique('nomor_order');
        });
    }
};
