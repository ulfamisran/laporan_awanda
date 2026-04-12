<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penggajian')) {
            return;
        }

        $this->recoverOrphanNewTableBesideLegacyBackup();

        if ($this->isAlreadyModul6Schema()) {
            $this->ensureModul6IndexesAndForeignKeys();

            return;
        }

        Schema::rename('penggajian', 'penggajian_old');

        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('relawan_id');
            $table->unsignedBigInteger('profil_mbg_id');
            $table->unsignedTinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('tunjangan_transport', 15, 2)->default(0);
            $table->decimal('tunjangan_makan', 15, 2)->default(0);
            $table->decimal('tunjangan_lainnya', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);
            $table->string('keterangan_potongan')->nullable();
            $table->decimal('total_gaji', 15, 2);
            $table->date('tanggal_bayar')->nullable();
            $table->string('status', 20)->default('draft');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->unique(['relawan_id', 'periode_bulan', 'periode_tahun'], 'penggajian_relawan_periode_unique');

            $table->foreign('relawan_id', 'penggajian_v6_relawan_fk')->references('id')->on('relawans')->restrictOnDelete();
            $table->foreign('profil_mbg_id', 'penggajian_v6_profil_fk')->references('id')->on('profil_mbg')->restrictOnDelete();
            $table->foreign('created_by', 'penggajian_v6_created_fk')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by', 'penggajian_v6_approved_fk')->references('id')->on('users')->nullOnDelete();
        });

        $defaultUserId = DB::table('users')->orderBy('id')->value('id');
        if (! $defaultUserId) {
            // migrate:fresh: belum ada user (seeder belum jalan). Pertahankan skema modul6 yang sudah dibuat.
            Schema::dropIfExists('penggajian_old');

            return;
        }

        $now = now()->toDateTimeString();
        $oldRows = DB::table('penggajian_old')->orderBy('id')->get();

        foreach ($oldRows as $o) {
            $rel = DB::table('relawans')->where('id', $o->relawan_id)->first();
            if (! $rel) {
                continue;
            }

            $c = Carbon::parse($o->periode);
            $gajiPokok = round((float) $rel->gaji_pokok, 2);
            $totalLama = round((float) $o->jumlah, 2);

            DB::table('penggajian')->insert([
                'relawan_id' => $o->relawan_id,
                'profil_mbg_id' => $rel->profil_mbg_id,
                'periode_bulan' => $c->month,
                'periode_tahun' => $c->year,
                'gaji_pokok' => $gajiPokok,
                'tunjangan_transport' => 0,
                'tunjangan_makan' => 0,
                'tunjangan_lainnya' => 0,
                'potongan' => 0,
                'keterangan_potongan' => null,
                'total_gaji' => $totalLama,
                'tanggal_bayar' => $c->copy()->endOfMonth()->toDateString(),
                'status' => 'dibayar',
                'catatan' => $o->keterangan,
                'created_by' => $defaultUserId,
                'approved_by' => null,
                'created_at' => $o->created_at ?? $now,
                'updated_at' => $o->updated_at ?? $now,
            ]);
        }

        Schema::dropIfExists('penggajian_old');
    }

    /**
     * Failed run can leave an empty modul-6 `penggajian` beside `penggajian_old` (legacy).
     * MySQL keeps FK *names* on RENAME TABLE, so the new table cannot reuse `penggajian_*_foreign`.
     */
    private function recoverOrphanNewTableBesideLegacyBackup(): void
    {
        if (! Schema::hasTable('penggajian') || ! Schema::hasTable('penggajian_old')) {
            return;
        }

        if (! Schema::hasColumn('penggajian', 'periode_bulan') || ! Schema::hasColumn('penggajian_old', 'periode')) {
            return;
        }

        if (DB::table('penggajian')->count() > 0) {
            return;
        }

        Schema::drop('penggajian');
        Schema::rename('penggajian_old', 'penggajian');
    }

    private function isAlreadyModul6Schema(): bool
    {
        return Schema::hasColumn('penggajian', 'periode_bulan')
            && ! Schema::hasColumn('penggajian', 'periode');
    }

    private function ensureModul6IndexesAndForeignKeys(): void
    {
        $indexes = Schema::getIndexes('penggajian');
        $hasUnique = collect($indexes)->contains(fn (array $idx) => $idx['name'] === 'penggajian_relawan_periode_unique');
        if (! $hasUnique) {
            Schema::table('penggajian', function (Blueprint $table) {
                $table->unique(['relawan_id', 'periode_bulan', 'periode_tahun'], 'penggajian_relawan_periode_unique');
            });
        }

        $driver = Schema::getConnection()->getDriverName();
        $fks = [];
        if ($driver === 'mysql') {
            $fks = collect(DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
                ['penggajian', 'FOREIGN KEY']
            ))->pluck('CONSTRAINT_NAME')->all();
        }

        Schema::table('penggajian', function (Blueprint $table) use ($fks) {
            if (! in_array('penggajian_v6_relawan_fk', $fks, true)) {
                $table->foreign('relawan_id', 'penggajian_v6_relawan_fk')->references('id')->on('relawans')->restrictOnDelete();
            }
            if (! in_array('penggajian_v6_profil_fk', $fks, true)) {
                $table->foreign('profil_mbg_id', 'penggajian_v6_profil_fk')->references('id')->on('profil_mbg')->restrictOnDelete();
            }
            if (! in_array('penggajian_v6_created_fk', $fks, true)) {
                $table->foreign('created_by', 'penggajian_v6_created_fk')->references('id')->on('users')->restrictOnDelete();
            }
            if (! in_array('penggajian_v6_approved_fk', $fks, true)) {
                $table->foreign('approved_by', 'penggajian_v6_approved_fk')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('penggajian')) {
            return;
        }

        Schema::create('penggajian_legacy_down', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relawan_id')->constrained('relawans')->restrictOnDelete();
            $table->date('periode');
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['relawan_id', 'periode']);
        });

        $rows = DB::table('penggajian')->orderBy('id')->get();
        foreach ($rows as $p) {
            $periode = sprintf('%04d-%02d-01', (int) $p->periode_tahun, (int) $p->periode_bulan);
            DB::table('penggajian_legacy_down')->insert([
                'relawan_id' => $p->relawan_id,
                'periode' => $periode,
                'jumlah' => $p->total_gaji,
                'keterangan' => $p->catatan,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);
        }

        Schema::dropIfExists('penggajian');
        Schema::rename('penggajian_legacy_down', 'penggajian');
    }
};
