<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function relawans(): HasMany
    {
        return $this->hasMany(Relawan::class, 'posisi_relawan_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama_posisi')->orderBy('id');
    }
}
