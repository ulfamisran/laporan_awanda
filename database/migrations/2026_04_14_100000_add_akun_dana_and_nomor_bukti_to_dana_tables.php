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
            $table->foreignId('akun_dana_id')->nullable()->after('kategori_dana_masuk_id')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor_bukti', 64)->nullable()->after('sumber');
        });

        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->foreignId('akun_dana_id')->nullable()->after('kategori_dana_keluar_id')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor_bukti', 64)->nullable()->after('keperluan');
        });

        $kasBank = DB::table('akun_dana')->where('kode', '1102')->value('id');
        $fallbackAkun = $kasBank ?? DB::table('akun_dana')->where('is_grup', 0)->orderBy('urutan')->orderBy('kode')->value('id');
        if ($fallbackAkun) {
            DB::table('dana_masuk')->whereNull('akun_dana_id')->update(['akun_dana_id' => $fallbackAkun]);
            DB::table('dana_keluar')->whereNull('akun_dana_id')->update(['akun_dana_id' => $fallbackAkun]);
        }
        DB::table('dana_masuk')->whereNull('nomor_bukti')->update(['nomor_bukti' => '-']);
        DB::table('dana_keluar')->whereNull('nomor_bukti')->update(['nomor_bukti' => '-']);
    }

    public function down(): void
    {
        Schema::table('dana_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('akun_dana_id');
            $table->dropColumn('nomor_bukti');
        });

        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('akun_dana_id');
            $table->dropColumn('nomor_bukti');
        });
    }
};
