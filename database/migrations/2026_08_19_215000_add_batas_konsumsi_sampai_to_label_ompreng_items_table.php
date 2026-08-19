<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('label_ompreng_items', function (Blueprint $table) {
            if (! Schema::hasColumn('label_ompreng_items', 'batas_konsumsi_sampai')) {
                $table->time('batas_konsumsi_sampai')->nullable()->after('batas_konsumsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('label_ompreng_items', function (Blueprint $table) {
            $table->dropColumn('batas_konsumsi_sampai');
        });
    }
};
