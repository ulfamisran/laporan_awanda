<?php

namespace App\Models;

use App\Enums\BarangMasukSumber;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';

    protected $fillable = [
        'kode_transaksi',
        'barang_id',
        'profil_mbg_id',
        'periode_id',
        'tanggal',
        'jumlah',
        'satuan',
        'harga_satuan',
        'total_harga',
        'sumber',
        'keterangan',
        'gambar',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'decimal:2',
            'harga_satuan' => 'decimal:2',
            'total_harga' => 'decimal:2',
            'sumber' => BarangMasukSumber::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BarangMasuk $row): void {
            $j = (float) $row->jumlah;
            $h = (float) $row->harga_satuan;
            $row->total_harga = round($j * $h, 2);
        });
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
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

    protected function gambarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->gambar) {
                return null;
            }

            // Same-origin path avoids broken images when APP_URL host differs from the browser URL.
            return '/storage/barang-masuk/'.$this->gambar;
        });
    }
}
