<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosisiRelawan extends Model
{
    use SoftDeletes;

    protected $table = 'posisi_relawan';

    protected $fillable = [
        'nama_posisi',
        'deskripsi',
    ];

    public function relawans(): HasMany
    {
        return $this->hasMany(Relawan::class, 'posisi_relawan_id');
    }
}
