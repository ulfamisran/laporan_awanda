<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderBarangItem extends Model
{
    protected $table = 'order_barang_items';

    protected $fillable = [
        'order_barang_id',
        'barang_id',
        'supplier_id',
        'nama_barang',
        'harga_barang',
        'jumlah_barang',
        'satuan_barang',
        'jumlah_hari_pemakaian',
    ];

    protected function casts(): array
    {
        return [
            'harga_barang' => 'decimal:2',
            'jumlah_barang' => 'decimal:2',
            'jumlah_hari_pemakaian' => 'integer',
        ];
    }

    public function orderBarang(): BelongsTo
    {
        return $this->belongsTo(OrderBarang::class, 'order_barang_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function penerimaan(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'order_barang_item_id');
    }
}
