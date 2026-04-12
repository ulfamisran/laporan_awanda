<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_mbg', function (Blueprint $table) {
            if (! Schema::hasColumn('profil_mbg', 'id_sppg')) {
                $table->string('id_sppg', 64)->nullable()->after('kode_dapur');
            }
            if (! Schema::hasColumn('profil_mbg', 'nama_yayasan')) {
                $table->string('nama_yayasan', 255)->nullable()->after('nama_ahli_gizi');
            }
            if (! Schema::hasColumn('profil_mbg', 'ketua_yayasan')) {
                $table->string('ketua_yayasan', 255)->nullable()->after('nama_yayasan');
            }
            if (! Schema::hasColumn('profil_mbg', 'nomor_rekening_va')) {
                $table->string('nomor_rekening_va', 128)->nullable()->after('ketua_yayasan');
            }
            if (! Schema::hasColumn('profil_mbg', 'tahun_anggaran')) {
                $table->unsignedSmallInteger('tahun_anggaran')->nullable()->after('nomor_rekening_va');
            }
            if (! Schema::hasColumn('profil_mbg', 'tempat_pelaporan')) {
                $table->string('tempat_pelaporan', 255)->nullable()->after('tahun_anggaran');
            }
        });

        Schema::table('profil_mbg', function (Blueprint $table) {
            if (Schema::hasColumn('profil_mbg', 'id_sppg')) {
                $table->unique('id_sppg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profil_mbg', function (Blueprint $table) {
            if (Schema::hasColumn('profil_mbg', 'id_sppg')) {
                $table->dropUnique(['id_sppg']);
            }
        });

        Schema::table('profil_mbg', function (Blueprint $table) {
            $cols = ['id_sppg', 'nama_yayasan', 'ketua_yayasan', 'nomor_rekening_va', 'tahun_anggaran', 'tempat_pelaporan'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('profil_mbg', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
