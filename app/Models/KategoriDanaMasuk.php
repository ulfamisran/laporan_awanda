<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriDanaMasuk extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_dana_masuk';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];
}
