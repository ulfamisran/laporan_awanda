<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('relawans') || Schema::hasColumn('relawans', 'gaji_per_hari')) {
            return;
        }

        Schema::table('relawans', function (Blueprint $table) {
            $table->decimal('gaji_per_hari', 15, 2)->default(0)->after('gaji_pokok');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('relawans') || ! Schema::hasColumn('relawans', 'gaji_per_hari')) {
            return;
        }

        Schema::table('relawans', function (Blueprint $table) {
            $table->dropColumn('gaji_per_hari');
        });
    }
};
