<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('barang_masuk')) {
            return;
        }

        Schema::table('barang_masuk', function (Blueprint $table) {
            if (! Schema::hasColumn('barang_masuk', 'order_barang_item_id')) {
                $table->foreignId('order_barang_item_id')
                    ->nullable()
                    ->after('barang_id')
                    ->constrained('order_barang_items')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('barang_masuk', 'kondisi_penerimaan')) {
                $table->string('kondisi_penerimaan', 255)->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('barang_masuk')) {
            return;
        }

        Schema::table('barang_masuk', function (Blueprint $table) {
            if (Schema::hasColumn('barang_masuk', 'order_barang_item_id')) {
                $table->dropConstrainedForeignId('order_barang_item_id');
            }
            if (Schema::hasColumn('barang_masuk', 'kondisi_penerimaan')) {
                $table->dropColumn('kondisi_penerimaan');
            }
        });
    }
};
