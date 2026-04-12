<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->repairAbortedPeriodeMigration();

        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nama', 191)->nullable();
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('status', 20)->default('nonaktif');
            $table->timestamps();
        });

        $profilIds = DB::table('profil_mbg')->orderBy('id')->pluck('id');
        $periodeByProfil = [];
        foreach ($profilIds as $profilId) {
            $periodeByProfil[(int) $profilId] = DB::table('periode')->insertGetId([
                'profil_mbg_id' => (int) $profilId,
                'nama' => 'Periode awal (migrasi)',
                'tanggal_awal' => '2020-01-01',
                'tanggal_akhir' => '2037-12-31',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $backfillPeriodeByProfil = function (string $table) use ($periodeByProfil): void {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'periode_id')) {
                return;
            }
            foreach ($periodeByProfil as $profilId => $periodeId) {
                DB::table($table)->where('profil_mbg_id', $profilId)->whereNull('periode_id')->update(['periode_id' => $periodeId]);
            }
        };

        $wire = function (string $table) use ($backfillPeriodeByProfil): void {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'periode_id')) {
                return;
            }
            Schema::table($table, function (Blueprint $table): void {
                $table->foreignId('periode_id')->nullable()->after('profil_mbg_id')->constrained('periode')->cascadeOnUpdate()->restrictOnDelete();
            });
            $backfillPeriodeByProfil($table);
        };

        foreach (['barang_masuk', 'barang_keluar', 'laporan_limbah', 'dana_masuk', 'dana_keluar'] as $t) {
            $wire($t);
        }

        if (Schema::hasTable('stok_awal_barang') && ! Schema::hasColumn('stok_awal_barang', 'periode_id')) {
            Schema::table('stok_awal_barang', function (Blueprint $table): void {
                $table->dropForeign(['barang_id']);
                $table->dropForeign(['profil_mbg_id']);
                $table->dropForeign(['created_by']);
            });
            $stokDup = collect(Schema::getIndexes('stok_awal_barang'))
                ->first(function (array $idx): bool {
                    if (empty($idx['unique'])) {
                        return false;
                    }
                    $cols = $idx['columns'] ?? [];

                    return $cols === ['barang_id', 'profil_mbg_id'];
                });
            if ($stokDup) {
                Schema::table('stok_awal_barang', function (Blueprint $table) use ($stokDup): void {
                    $table->dropUnique($stokDup['name']);
                });
            }
            Schema::table('stok_awal_barang', function (Blueprint $table): void {
                $table->foreignId('periode_id')->nullable()->after('profil_mbg_id')->constrained('periode')->cascadeOnUpdate()->restrictOnDelete();
            });
            $backfillPeriodeByProfil('stok_awal_barang');
            Schema::table('stok_awal_barang', function (Blueprint $table): void {
                $table->unique(['barang_id', 'profil_mbg_id', 'periode_id'], 'stok_awal_barang_barang_profil_periode_unique');
                $table->foreign('barang_id')->references('id')->on('barang')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreign('profil_mbg_id')->references('id')->on('profil_mbg')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        if (Schema::hasTable('penggajian') && ! Schema::hasColumn('penggajian', 'periode_id')) {
            $this->dropPenggajianForeignKeysForPeriodeWire();
            $pengDup = collect(Schema::getIndexes('penggajian'))
                ->first(function (array $idx): bool {
                    if (empty($idx['unique'])) {
                        return false;
                    }
                    $cols = $idx['columns'] ?? [];

                    return $cols === ['relawan_id', 'periode_bulan', 'periode_tahun'];
                });
            if ($pengDup) {
                Schema::table('penggajian', function (Blueprint $table) use ($pengDup): void {
                    $table->dropUnique($pengDup['name']);
                });
            }
            Schema::table('penggajian', function (Blueprint $table): void {
                $table->foreignId('periode_id')->nullable()->after('profil_mbg_id')->constrained('periode')->cascadeOnUpdate()->restrictOnDelete();
            });
            $backfillPeriodeByProfil('penggajian');
            Schema::table('penggajian', function (Blueprint $table): void {
                $table->unique(['relawan_id', 'periode_bulan', 'periode_tahun', 'periode_id'], 'penggajian_relawan_bulan_tahun_laporan_unique');
                $table->foreign('relawan_id', 'penggajian_v6_relawan_fk')->references('id')->on('relawans')->restrictOnDelete();
                $table->foreign('profil_mbg_id', 'penggajian_v6_profil_fk')->references('id')->on('profil_mbg')->restrictOnDelete();
                $table->foreign('created_by', 'penggajian_v6_created_fk')->references('id')->on('users')->restrictOnDelete();
                $table->foreign('approved_by', 'penggajian_v6_approved_fk')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('penggajian') && Schema::hasColumn('penggajian', 'periode_id')) {
            Schema::table('penggajian', function (Blueprint $table): void {
                $table->dropUnique('penggajian_relawan_bulan_tahun_laporan_unique');
                $table->dropConstrainedForeignId('periode_id');
            });
            Schema::table('penggajian', function (Blueprint $table): void {
                $table->unique(['relawan_id', 'periode_bulan', 'periode_tahun'], 'penggajian_relawan_periode_unique');
            });
        }

        if (Schema::hasTable('stok_awal_barang') && Schema::hasColumn('stok_awal_barang', 'periode_id')) {
            Schema::table('stok_awal_barang', function (Blueprint $table): void {
                $table->dropUnique('stok_awal_barang_barang_profil_periode_unique');
                $table->dropConstrainedForeignId('periode_id');
            });
            Schema::table('stok_awal_barang', function (Blueprint $table): void {
                $table->unique(['barang_id', 'profil_mbg_id']);
            });
        }

        foreach (['dana_keluar', 'dana_masuk', 'laporan_limbah', 'barang_keluar', 'barang_masuk'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'periode_id')) {
                Schema::table($t, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('periode_id');
                });
            }
        }

        Schema::dropIfExists('periode');
    }

    /**
     * Hapus FK pada kolom yang akan di-wire ulang. Nama constraint bisa `penggajian_v6_*`
     * atau default Laravel (`*_foreign`), sehingga drop nama tetap sering gagal di migrate:fresh.
     */
    private function dropPenggajianForeignKeysForPeriodeWire(): void
    {
        $columnsToRelease = ['relawan_id', 'profil_mbg_id', 'created_by', 'approved_by'];

        try {
            $foreignKeys = Schema::getForeignKeys('penggajian');
        } catch (\Throwable) {
            return;
        }

        $names = [];
        foreach ($foreignKeys as $fk) {
            $cols = $fk['columns'] ?? [];
            if ($cols === [] || array_intersect($columnsToRelease, $cols) === []) {
                continue;
            }
            $name = $fk['name'] ?? null;
            if ($name) {
                $names[] = $name;
            }
        }

        foreach ($names as $name) {
            Schema::table('penggajian', function (Blueprint $table) use ($name): void {
                $table->dropForeign($name);
            });
        }
    }

    /**
     * MySQL can leave `periode` + wired tables behind if a later DDL step fails mid-migration.
     */
    private function repairAbortedPeriodeMigration(): void
    {
        if (! Schema::hasTable('periode')) {
            return;
        }

        $stokHasPeriode = Schema::hasTable('stok_awal_barang') && Schema::hasColumn('stok_awal_barang', 'periode_id');
        if ($stokHasPeriode) {
            return;
        }

        foreach (['penggajian', 'stok_awal_barang', 'dana_keluar', 'dana_masuk', 'laporan_limbah', 'barang_keluar', 'barang_masuk'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'periode_id')) {
                Schema::table($t, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('periode_id');
                });
            }
        }

        Schema::dropIfExists('periode');
    }
};
