<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriDanaKeluar extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_dana_keluar';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];
}
