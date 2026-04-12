<?php

namespace App\Models;

use App\Enums\JenisPenangananLimbah;
use App\Enums\SatuanLimbah;
use App\Support\LimbahVolumeKg;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanLimbah extends Model
{
    protected $table = 'laporan_limbah';

    protected $fillable = [
        'harian_id',
        'kode_laporan',
        'kategori_limbah_id',
        'profil_mbg_id',
        'periode_id',
        'tanggal',
        'jumlah',
        'satuan',
        'jenis_penanganan',
        'harga_jual',
        'keterangan',
        'gambar',
        'created_by',
    ];

    protected $appends = [
        'gambar_url',
    ];

    protected function casts(): array
    {
        return [
            'harian_id' => 'integer',
            'tanggal' => 'date',
            'jumlah' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'satuan' => SatuanLimbah::class,
            'jenis_penanganan' => JenisPenangananLimbah::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (LaporanLimbah $m): void {
            if ($m->jenis_penanganan !== JenisPenangananLimbah::Dijual) {
                $m->harga_jual = null;
            }
        });
    }

    public function harian(): BelongsTo
    {
        return $this->belongsTo(LaporanLimbahHarian::class, 'harian_id');
    }

    public function kategoriLimbah(): BelongsTo
    {
        return $this->belongsTo(KategoriLimbah::class, 'kategori_limbah_id');
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

    public function scopePeriode(Builder $query, string $dari, string $sampai): Builder
    {
        return $query->whereBetween('tanggal', [$dari, $sampai]);
    }

    public function scopeByDapur(Builder $query, int $profilMbgId): Builder
    {
        return $query->where('profil_mbg_id', $profilMbgId);
    }

    public function volumeKgEstimate(): float
    {
        $satuan = $this->satuan instanceof SatuanLimbah
            ? $this->satuan
            : SatuanLimbah::from((string) $this->satuan);

        return LimbahVolumeKg::estimate((float) $this->jumlah, $satuan);
    }

    protected function gambarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->gambar) {
                return null;
            }

            // Same-origin path so images load when APP_URL host differs from the browser URL.
            return '/storage/foto-limbah/'.$this->gambar;
        });
    }
}
