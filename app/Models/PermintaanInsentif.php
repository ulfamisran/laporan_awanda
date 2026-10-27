<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanInsentif extends Model
{
    protected $fillable = [
        'profil_mbg_id',
        'periode_id',
        'judul_surat',
        'periode_label',
        'program',
        'nama_sppg',
        'nama_peminta',
        'jabatan_peminta',
        'tanggal_perihal',
        'jumlah',
        'perihal_teks',
        'catatan',
        'tempat_ttd',
        'tanggal_ttd',
        'ttd_mengetahui_jabatan',
        'ttd_mengetahui_nama',
        'ttd_menyetujui_jabatan',
        'ttd_menyetujui_nama',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'profil_mbg_id' => 'integer',
            'periode_id' => 'integer',
            'tanggal_perihal' => 'date',
            'tanggal_ttd' => 'date',
            'jumlah' => 'decimal:2',
            'created_by' => 'integer',
        ];
    }

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
