<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelOmprengItem extends Model
{
    protected $table = 'label_ompreng_items';

    protected $fillable = [
        'label_ompreng_id',
        'urutan',
        'menu_1',
        'menu_2',
        'menu_3',
        'menu_4',
        'menu_5',
        'energi_nilai',
        'energi_satuan',
        'protein_nilai',
        'protein_satuan',
        'lemak_nilai',
        'lemak_satuan',
        'karbohidrat_nilai',
        'karbohidrat_satuan',
        'serat_nilai',
        'serat_satuan',
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
        'tanggal_produksi',
        'batas_konsumsi',
        'batas_konsumsi_sampai',
    ];

    protected function casts(): array
    {
        return [
            'energi_nilai' => 'decimal:2',
            'protein_nilai' => 'decimal:2',
            'lemak_nilai' => 'decimal:2',
            'karbohidrat_nilai' => 'decimal:2',
            'serat_nilai' => 'decimal:2',
            'porsi_besar_energi_nilai' => 'decimal:2',
            'porsi_besar_protein_nilai' => 'decimal:2',
            'porsi_besar_lemak_nilai' => 'decimal:2',
            'porsi_besar_karbohidrat_nilai' => 'decimal:2',
            'porsi_besar_serat_nilai' => 'decimal:2',
            'porsi_kecil_energi_nilai' => 'decimal:2',
            'porsi_kecil_protein_nilai' => 'decimal:2',
            'porsi_kecil_lemak_nilai' => 'decimal:2',
            'porsi_kecil_karbohidrat_nilai' => 'decimal:2',
            'porsi_kecil_serat_nilai' => 'decimal:2',
            'balita_energi_nilai' => 'decimal:2',
            'balita_protein_nilai' => 'decimal:2',
            'balita_lemak_nilai' => 'decimal:2',
            'balita_karbohidrat_nilai' => 'decimal:2',
            'balita_serat_nilai' => 'decimal:2',
            'bumil_busui_energi_nilai' => 'decimal:2',
            'bumil_busui_protein_nilai' => 'decimal:2',
            'bumil_busui_lemak_nilai' => 'decimal:2',
            'bumil_busui_karbohidrat_nilai' => 'decimal:2',
            'bumil_busui_serat_nilai' => 'decimal:2',
            'tanggal_produksi' => 'date',
        ];
    }

    public function labelOmpreng(): BelongsTo
    {
        return $this->belongsTo(LabelOmpreng::class, 'label_ompreng_id');
    }
}
