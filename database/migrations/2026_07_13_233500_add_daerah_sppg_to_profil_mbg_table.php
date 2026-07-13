<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profil_mbg')) {
            return;
        }

        Schema::table('profil_mbg', function (Blueprint $table) {
            if (! Schema::hasColumn('profil_mbg', 'daerah_sppg')) {
                $table->string('daerah_sppg')->nullable()->after('nama_dapur');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('profil_mbg')) {
            return;
        }

        Schema::table('profil_mbg', function (Blueprint $table) {
            if (Schema::hasColumn('profil_mbg', 'daerah_sppg')) {
                $table->dropColumn('daerah_sppg');
            }
        });
    }
};
