<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderBarang extends Model
{
    protected $table = 'order_barang';

    protected $fillable = [
        'nomor_order',
        'profil_mbg_id',
        'periode_id',
        'tanggal_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_order' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderBarangItem::class, 'order_barang_id');
    }

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }
}
