<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_barang_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_barang_items', 'supplier_nama')) {
                $table->string('supplier_nama')->nullable()->after('nama_barang');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_barang_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_barang_items', 'supplier_nama')) {
                $table->dropColumn('supplier_nama');
            }
        });
    }
};
