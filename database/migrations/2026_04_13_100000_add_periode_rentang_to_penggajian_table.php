<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penggajian')) {
            return;
        }

        Schema::table('penggajian', function (Blueprint $table) {
            if (! Schema::hasColumn('penggajian', 'periode_mulai')) {
                $table->date('periode_mulai')->nullable()->after('periode_tahun');
            }
            if (! Schema::hasColumn('penggajian', 'periode_selesai')) {
                $table->date('periode_selesai')->nullable()->after('periode_mulai');
            }
        });

        $indexes = collect(Schema::getIndexes('penggajian'));
        if ($indexes->contains(fn (array $idx) => ($idx['name'] ?? '') === 'penggajian_relawan_periode_unique')) {
            Schema::table('penggajian', function (Blueprint $table) {
                $table->dropUnique('penggajian_relawan_periode_unique');
            });
        }

        $hasNewUnique = $indexes->contains(fn (array $idx) => ($idx['name'] ?? '') === 'penggajian_relawan_rentang_unique');
        if (! $hasNewUnique) {
            Schema::table('penggajian', function (Blueprint $table) {
                $table->unique(['relawan_id', 'periode_mulai', 'periode_selesai'], 'penggajian_relawan_rentang_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('penggajian')) {
            return;
        }

        $indexes = collect(Schema::getIndexes('penggajian'));
        if ($indexes->contains(fn (array $idx) => ($idx['name'] ?? '') === 'penggajian_relawan_rentang_unique')) {
            Schema::table('penggajian', function (Blueprint $table) {
                $table->dropUnique('penggajian_relawan_rentang_unique');
            });
        }

        if (! $indexes->contains(fn (array $idx) => ($idx['name'] ?? '') === 'penggajian_relawan_periode_unique')) {
            Schema::table('penggajian', function (Blueprint $table) {
                $table->unique(['relawan_id', 'periode_bulan', 'periode_tahun'], 'penggajian_relawan_periode_unique');
            });
        }

        Schema::table('penggajian', function (Blueprint $table) {
            if (Schema::hasColumn('penggajian', 'periode_selesai')) {
                $table->dropColumn('periode_selesai');
            }
            if (Schema::hasColumn('penggajian', 'periode_mulai')) {
                $table->dropColumn('periode_mulai');
            }
        });
    }
};
