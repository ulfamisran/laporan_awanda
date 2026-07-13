<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'nama_bank')) {
                $table->string('nama_bank')->nullable()->after('alamat');
            }
            if (! Schema::hasColumn('suppliers', 'nomor_rekening')) {
                $table->string('nomor_rekening', 64)->nullable()->after('nama_bank');
            }
            if (! Schema::hasColumn('suppliers', 'atas_nama_rekening')) {
                $table->string('atas_nama_rekening')->nullable()->after('nomor_rekening');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            foreach (['atas_nama_rekening', 'nomor_rekening', 'nama_bank'] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
