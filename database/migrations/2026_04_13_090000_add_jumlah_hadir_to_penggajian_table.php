<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penggajian') || Schema::hasColumn('penggajian', 'jumlah_hadir')) {
            return;
        }

        Schema::table('penggajian', function (Blueprint $table) {
            $table->unsignedTinyInteger('jumlah_hadir')->default(0)->after('periode_tahun');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('penggajian') || ! Schema::hasColumn('penggajian', 'jumlah_hadir')) {
            return;
        }

        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropColumn('jumlah_hadir');
        });
    }
};
