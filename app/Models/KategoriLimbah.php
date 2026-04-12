<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriLimbah extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_limbah';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function laporanLimbah(): HasMany
    {
        return $this->hasMany(LaporanLimbah::class, 'kategori_limbah_id');
    }
}
