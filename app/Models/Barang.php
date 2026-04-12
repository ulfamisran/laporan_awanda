<?php

namespace App\Models;

use App\Enums\SatuanBarang;
use App\Enums\StatusAktif;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Barang extends Model
{
    use SoftDeletes;

    protected $table = 'barang';

    protected $fillable = [
        'nama_barang',
        'kategori_barang_id',
        'satuan',
        'harga_satuan',
        'stok_minimum',
        'deskripsi',
        'foto',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'satuan' => SatuanBarang::class,
            'status' => StatusAktif::class,
            'harga_satuan' => 'decimal:2',
            'stok_minimum' => 'decimal:4',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Barang $barang): void {
            if ($barang->kode_barang === null || $barang->kode_barang === '') {
                $barang->kode_barang = static::generateUniqueKode();
            }
        });
    }

    public static function previewNextKode(): string
    {
        $prefix = 'BRG-'.now()->format('Ymd').'-';

        $last = static::withTrashed()
            ->where('kode_barang', 'like', $prefix.'%')
            ->orderByDesc('kode_barang')
            ->value('kode_barang');

        $next = 1;
        if ($last && preg_match('/-(\d{3})$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public static function generateUniqueKode(): string
    {
        return DB::transaction(function (): string {
            $prefix = 'BRG-'.now()->format('Ymd').'-';

            $last = static::withTrashed()
                ->where('kode_barang', 'like', $prefix.'%')
                ->orderByDesc('kode_barang')
                ->lockForUpdate()
                ->value('kode_barang');

            $next = 1;
            if ($last && preg_match('/-(\d{3})$/', $last, $m)) {
                $next = (int) $m[1] + 1;
            }

            return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    public function kategoriBarang(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_barang_id');
    }

    public function stokAwal(): HasMany
    {
        return $this->hasMany(StokAwal::class, 'barang_id');
    }

    public function barangMasukItems(): HasMany
    {
        return $this->hasMany(BarangMasukItem::class, 'barang_id');
    }

    public function barangKeluarItems(): HasMany
    {
        return $this->hasMany(BarangKeluarItem::class, 'barang_id');
    }

    public function stokAwalBarangs(): HasMany
    {
        return $this->hasMany(StokAwalBarang::class, 'barang_id');
    }

    public function barangMasuks(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'barang_id');
    }

    public function barangKeluars(): HasMany
    {
        return $this->hasMany(BarangKeluar::class, 'barang_id');
    }

    /**
     * Stok per dapur dan periode laporan. Jika $profilMbgId null, dijumlahkan semua dapur.
     * Jika $periodeId null, tidak difilter periode (hindari di UI transaksi).
     */
    public function getStokSaatIni(?int $profilMbgId = null, ?int $periodeId = null): float
    {
        $awal = $this->stokAwalBarangs()
            ->when($profilMbgId !== null, fn ($q) => $q->where('profil_mbg_id', $profilMbgId))
            ->when($periodeId !== null, fn ($q) => $q->where('periode_id', $periodeId))
            ->sum('jumlah');

        $masuk = $this->barangMasuks()
            ->when($profilMbgId !== null, fn ($q) => $q->where('profil_mbg_id', $profilMbgId))
            ->when($periodeId !== null, fn ($q) => $q->where('periode_id', $periodeId))
            ->sum('jumlah');

        $keluar = $this->barangKeluars()
            ->when($profilMbgId !== null, fn ($q) => $q->where('profil_mbg_id', $profilMbgId))
            ->when($periodeId !== null, fn ($q) => $q->where('periode_id', $periodeId))
            ->sum('jumlah');

        return (float) $awal + (float) $masuk - (float) $keluar;
    }

    /**
     * URL publik foto barang (accessor: foto_url).
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->foto) {
                return null;
            }

            return Storage::disk('public')->url('foto-barang/'.$this->foto);
        });
    }

    /**
     * Stok tampilan ringkas: profil & periode aktif (untuk daftar master bila pengguna login).
     */
    protected function stokSaatIni(): Attribute
    {
        return Attribute::get(function (): float {
            try {
                return $this->getStokSaatIni(ProfilMbgTenant::id(), PeriodeTenant::id());
            } catch (\Throwable) {
                return 0.0;
            }
        });
    }
}
