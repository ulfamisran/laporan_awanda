<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kode grup master di seeder akun dana. */
    private const KODE_GRUP_KAS = '1100';

    private const KODE_GRUP_JENIS_DANA = '2000';

    public function up(): void
    {
        foreach (['dana_masuk', 'dana_keluar'] as $tblName) {
            Schema::table($tblName, function (Blueprint $table) {
                $table->foreignId('akun_jenis_dana_id')->nullable()->after('nomor_bukti')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('akun_kas_id')->nullable()->after('akun_jenis_dana_id')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        $kasGrup = DB::table('akun_dana')->where('kode', self::KODE_GRUP_KAS)->value('id');
        $jenisGrup = DB::table('akun_dana')->where('kode', self::KODE_GRUP_JENIS_DANA)->value('id');
        $firstKas = $kasGrup
            ? DB::table('akun_dana')->where('parent_id', $kasGrup)->where('is_grup', 0)->orderBy('urutan')->orderBy('kode')->value('id')
            : null;
        $firstJenis = $jenisGrup
            ? DB::table('akun_dana')->where('parent_id', $jenisGrup)->where('is_grup', 0)->orderBy('urutan')->orderBy('kode')->value('id')
            : null;

        foreach (['dana_masuk', 'dana_keluar'] as $tbl) {
            if (! Schema::hasColumn($tbl, 'akun_dana_id')) {
                continue;
            }
            foreach (DB::table($tbl)->select('id', 'akun_dana_id')->get() as $row) {
                $j = $firstJenis;
                $k = $firstKas;
                $aid = $row->akun_dana_id ?? null;
                if ($aid && $kasGrup && $jenisGrup) {
                    $acc = DB::table('akun_dana')->where('id', $aid)->first();
                    if ($acc && ! (bool) $acc->is_grup) {
                        if ((int) $acc->parent_id === (int) $kasGrup) {
                            $k = (int) $aid;
                            $j = $firstJenis;
                        } elseif ((int) $acc->parent_id === (int) $jenisGrup) {
                            $j = (int) $aid;
                            $k = $firstKas;
                        }
                    }
                }
                DB::table($tbl)->where('id', $row->id)->update([
                    'akun_jenis_dana_id' => $j,
                    'akun_kas_id' => $k,
                ]);
            }
        }

        foreach (['dana_masuk', 'dana_keluar'] as $tbl) {
            if (Schema::hasColumn($tbl, 'akun_dana_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('akun_dana_id');
                });
            }
        }

        Schema::table('dana_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_dana_masuk_id');
        });
        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_dana_keluar_id');
        });
    }

    public function down(): void
    {
        Schema::table('dana_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('akun_jenis_dana_id');
            $table->dropConstrainedForeignId('akun_kas_id');
        });
        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('akun_jenis_dana_id');
            $table->dropConstrainedForeignId('akun_kas_id');
        });

        Schema::table('dana_masuk', function (Blueprint $table) {
            $table->foreignId('kategori_dana_masuk_id')->nullable()->after('kode_transaksi')->constrained('kategori_dana_masuk')->cascadeOnDelete();
            $table->foreignId('akun_dana_id')->nullable()->after('kategori_dana_masuk_id')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
        });
        Schema::table('dana_keluar', function (Blueprint $table) {
            $table->foreignId('kategori_dana_keluar_id')->nullable()->after('kode_transaksi')->constrained('kategori_dana_keluar')->cascadeOnDelete();
            $table->foreignId('akun_dana_id')->nullable()->after('kategori_dana_keluar_id')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
