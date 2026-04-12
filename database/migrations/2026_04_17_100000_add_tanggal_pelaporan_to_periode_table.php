<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->date('tanggal_pelaporan')->nullable()->after('tanggal_akhir');
        });

        DB::table('periode')->whereNull('tanggal_pelaporan')->update([
            'tanggal_pelaporan' => DB::raw('tanggal_akhir'),
        ]);
    }

    public function down(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn('tanggal_pelaporan');
        });
    }
};
