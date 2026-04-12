<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokDanaAwalAkun extends Model
{
    protected $table = 'stok_dana_awal_akun';

    protected $fillable = [
        'stok_dana_awal_id',
        'akun_dana_id',
        'saldo_awal',
    ];

    protected function casts(): array
    {
        return [
            'stok_dana_awal_id' => 'integer',
            'akun_dana_id' => 'integer',
            'saldo_awal' => 'decimal:2',
        ];
    }

    public function stokDanaAwal(): BelongsTo
    {
        return $this->belongsTo(StokDanaAwal::class, 'stok_dana_awal_id');
    }

    public function akunDana(): BelongsTo
    {
        return $this->belongsTo(AkunDana::class, 'akun_dana_id');
    }
}
