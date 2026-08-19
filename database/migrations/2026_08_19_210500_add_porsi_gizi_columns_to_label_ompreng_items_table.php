<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('label_ompreng_items')) {
            return;
        }

        Schema::table('label_ompreng_items', function (Blueprint $table) {
            $columns = [
                'porsi_besar_energi_nilai',
                'porsi_besar_protein_nilai',
                'porsi_besar_lemak_nilai',
                'porsi_besar_karbohidrat_nilai',
                'porsi_besar_serat_nilai',
                'porsi_kecil_energi_nilai',
                'porsi_kecil_protein_nilai',
                'porsi_kecil_lemak_nilai',
                'porsi_kecil_karbohidrat_nilai',
                'porsi_kecil_serat_nilai',
                'balita_energi_nilai',
                'balita_protein_nilai',
                'balita_lemak_nilai',
                'balita_karbohidrat_nilai',
                'balita_serat_nilai',
                'bumil_busui_energi_nilai',
                'bumil_busui_protein_nilai',
                'bumil_busui_lemak_nilai',
                'bumil_busui_karbohidrat_nilai',
                'bumil_busui_serat_nilai',
            ];

            foreach ($columns as $column) {
                if (! Schema::hasColumn('label_ompreng_items', $column)) {
                    $table->decimal($column, 10, 2)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('label_ompreng_items')) {
            return;
        }

        Schema::table('label_ompreng_items', function (Blueprint $table) {
            $columns = [
                'porsi_besar_energi_nilai',
                'porsi_besar_protein_nilai',
                'porsi_besar_lemak_nilai',
                'porsi_besar_karbohidrat_nilai',
                'porsi_besar_serat_nilai',
                'porsi_kecil_energi_nilai',
                'porsi_kecil_protein_nilai',
                'porsi_kecil_lemak_nilai',
                'porsi_kecil_karbohidrat_nilai',
                'porsi_kecil_serat_nilai',
                'balita_energi_nilai',
                'balita_protein_nilai',
                'balita_lemak_nilai',
                'balita_karbohidrat_nilai',
                'balita_serat_nilai',
                'bumil_busui_energi_nilai',
                'bumil_busui_protein_nilai',
                'bumil_busui_lemak_nilai',
                'bumil_busui_karbohidrat_nilai',
                'bumil_busui_serat_nilai',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('label_ompreng_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
