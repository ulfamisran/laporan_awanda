<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_barang_items')) {
            return;
        }

        Schema::table('order_barang_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_barang_items', 'nomor_nota')) {
                $table->string('nomor_nota')->nullable()->after('supplier_nama');
                $table->index('nomor_nota');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_barang_items')) {
            return;
        }

        Schema::table('order_barang_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_barang_items', 'nomor_nota')) {
                $table->dropIndex(['nomor_nota']);
                $table->dropColumn('nomor_nota');
            }
        });
    }
};
