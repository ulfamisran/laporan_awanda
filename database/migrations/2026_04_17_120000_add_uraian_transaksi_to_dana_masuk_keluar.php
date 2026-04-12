<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dana_masuk', function (Blueprint $table) {
            $table->text('uraian_transaksi')->nullable()->after('keterangan');
        });

        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->text('uraian_transaksi')->nullable()->after('keterangan');
        });

        DB::statement("UPDATE dana_masuk SET uraian_transaksi = CASE WHEN NULLIF(TRIM(COALESCE(keterangan, '')), '') IS NOT NULL THEN keterangan ELSE COALESCE(sumber, '') END WHERE uraian_transaksi IS NULL");
        DB::statement("UPDATE dana_keluar SET uraian_transaksi = CASE WHEN NULLIF(TRIM(COALESCE(keterangan, '')), '') IS NOT NULL THEN keterangan ELSE COALESCE(keperluan, '') END WHERE uraian_transaksi IS NULL");
    }

    public function down(): void
    {
        Schema::table('dana_masuk', function (Blueprint $table) {
            $table->dropColumn('uraian_transaksi');
        });

        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->dropColumn('uraian_transaksi');
        });
    }
};
