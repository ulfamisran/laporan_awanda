<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanLimbahHarian extends Model
{
    protected $table = 'laporan_limbah_harian';

    protected $hidden = [
        'profil_mbg_id',
        'periode_id',
        'created_by',
    ];

    protected $fillable = [
        'profil_mbg_id',
        'periode_id',
        'tanggal',
        'menu_makanan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
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

    /**
     * @return HasMany<LaporanLimbah, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(LaporanLimbah::class, 'harian_id')->orderBy('kategori_limbah_id');
    }
}
