<?php

namespace App\Models;

use App\Enums\StatusAktif;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Periode extends Model
{
    protected $table = 'periode';

    protected $fillable = [
        'profil_mbg_id',
        'nama',
        'tanggal_awal',
        'tanggal_akhir',
        'tanggal_pelaporan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_awal' => 'date',
            'tanggal_akhir' => 'date',
            'tanggal_pelaporan' => 'date',
            'status' => StatusAktif::class,
        ];
    }

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }

    public function labelRingkas(): string
    {
        $a = $this->tanggal_awal?->format('d/m/Y') ?? '';
        $b = $this->tanggal_akhir?->format('d/m/Y') ?? '';
        $nama = $this->nama ? ' — '.$this->nama : '';

        return $a.' – '.$b.$nama;
    }
}
