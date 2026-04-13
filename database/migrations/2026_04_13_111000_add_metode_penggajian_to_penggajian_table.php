<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penggajian') || Schema::hasColumn('penggajian', 'metode_penggajian')) {
            return;
        }

        Schema::table('penggajian', function (Blueprint $table) {
            $table->string('metode_penggajian', 20)->default('gaji_pokok')->after('periode_selesai');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('penggajian') || ! Schema::hasColumn('penggajian', 'metode_penggajian')) {
            return;
        }

        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropColumn('metode_penggajian');
        });
    }
};
