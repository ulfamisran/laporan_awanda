<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posisi_relawan', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->default(0)->after('deskripsi');
        });

        $rows = DB::table('posisi_relawan')
            ->orderBy('nama_posisi')
            ->orderBy('id')
            ->get(['id']);

        $i = 10;
        foreach ($rows as $row) {
            DB::table('posisi_relawan')->where('id', $row->id)->update(['urutan' => $i]);
            $i += 10;
        }
    }

    public function down(): void
    {
        Schema::table('posisi_relawan', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
