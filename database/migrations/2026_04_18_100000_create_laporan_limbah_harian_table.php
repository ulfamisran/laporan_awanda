<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_limbah_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_mbg_id')->constrained('profil_mbg')->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
            $table->date('tanggal');
            $table->string('menu_makanan', 1000);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['profil_mbg_id', 'periode_id', 'tanggal']);
        });

        Schema::table('laporan_limbah', function (Blueprint $table) {
            $table->foreignId('harian_id')->nullable()->after('periode_id')->constrained('laporan_limbah_harian')->cascadeOnDelete();
        });

        $this->backfillHarian();
    }

    private function backfillHarian(): void
    {
        if (! Schema::hasTable('laporan_limbah') || ! Schema::hasColumn('laporan_limbah', 'harian_id')) {
            return;
        }

        $rows = DB::table('laporan_limbah')
            ->select('profil_mbg_id', 'periode_id', 'tanggal', DB::raw('MIN(created_by) as created_by'))
            ->whereNotNull('periode_id')
            ->whereNull('harian_id')
            ->groupBy('profil_mbg_id', 'periode_id', 'tanggal')
            ->get();

        foreach ($rows as $g) {
            $createdBy = (int) ($g->created_by ?? 0);
            if ($createdBy <= 0) {
                $createdBy = (int) (DB::table('users')->orderBy('id')->value('id') ?? 1);
            }

            $harianId = DB::table('laporan_limbah_harian')->insertGetId([
                'profil_mbg_id' => (int) $g->profil_mbg_id,
                'periode_id' => (int) $g->periode_id,
                'tanggal' => (string) $g->tanggal,
                'menu_makanan' => '—',
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('laporan_limbah')
                ->where('profil_mbg_id', (int) $g->profil_mbg_id)
                ->where('periode_id', (int) $g->periode_id)
                ->whereDate('tanggal', (string) $g->tanggal)
                ->whereNull('harian_id')
                ->update(['harian_id' => $harianId]);
        }
    }

    public function down(): void
    {
        Schema::table('laporan_limbah', function (Blueprint $table) {
            $table->dropConstrainedForeignId('harian_id');
        });

        Schema::dropIfExists('laporan_limbah_harian');
    }
};
