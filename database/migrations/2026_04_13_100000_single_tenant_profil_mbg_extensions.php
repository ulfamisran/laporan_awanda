<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_mbg', function (Blueprint $table) {
            if (! Schema::hasColumn('profil_mbg', 'nama_akuntansi')) {
                $table->string('nama_akuntansi')->nullable()->after('penanggung_jawab');
            }
            if (! Schema::hasColumn('profil_mbg', 'nama_ahli_gizi')) {
                $table->string('nama_ahli_gizi')->nullable()->after('nama_akuntansi');
            }
        });

        if (DB::table('profil_mbg')->count() === 0) {
            DB::table('profil_mbg')->insert([
                'nama_dapur' => 'Cabang MBG',
                'kode_dapur' => 'MBG-01',
                'alamat' => null,
                'kota' => null,
                'provinsi' => null,
                'no_telp' => null,
                'penanggung_jawab' => null,
                'nama_akuntansi' => null,
                'nama_ahli_gizi' => null,
                'logo' => null,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('profil_mbg', function (Blueprint $table) {
            if (Schema::hasColumn('profil_mbg', 'nama_ahli_gizi')) {
                $table->dropColumn('nama_ahli_gizi');
            }
            if (Schema::hasColumn('profil_mbg', 'nama_akuntansi')) {
                $table->dropColumn('nama_akuntansi');
            }
        });
    }
};
