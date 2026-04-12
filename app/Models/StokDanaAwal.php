<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokDanaAwal extends Model
{
    protected $table = 'stok_dana_awal';

    protected $fillable = [
        'profil_mbg_id',
        'tanggal',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'profil_mbg_id' => 'integer',
            'tanggal' => 'date',
            'created_by' => 'integer',
        ];
    }

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StokDanaAwalAkun::class, 'stok_dana_awal_id');
    }
}
