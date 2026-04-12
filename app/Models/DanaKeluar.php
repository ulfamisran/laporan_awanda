<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DanaKeluar extends Model
{
    protected $table = 'dana_keluar';

    protected $fillable = [
        'kode_transaksi',
        'akun_jenis_dana_id',
        'akun_kas_id',
        'profil_mbg_id',
        'periode_id',
        'tanggal',
        'jumlah',
        'nomor_bukti',
        'keperluan',
        'keterangan',
        'uraian_transaksi',
        'gambar_nota',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'akun_jenis_dana_id' => 'integer',
            'akun_kas_id' => 'integer',
            'profil_mbg_id' => 'integer',
            'tanggal' => 'date',
            'jumlah' => 'decimal:2',
            'gambar_nota' => 'array',
            'created_by' => 'integer',
        ];
    }

    public function akunJenisDana(): BelongsTo
    {
        return $this->belongsTo(AkunDana::class, 'akun_jenis_dana_id');
    }

    public function akunKas(): BelongsTo
    {
        return $this->belongsTo(AkunDana::class, 'akun_kas_id');
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

    public function ringkasanBukuPembantu(): string
    {
        $fmt = fn (?AkunDana $a): string => $a ? ($a->kode.' — '.$a->nama) : '—';

        return $fmt($this->akunJenisDana).' · '.$fmt($this->akunKas);
    }

    protected function gambarNotaUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $paths = $this->gambar_nota ?? [];
            $first = is_array($paths) && $paths !== [] ? (string) reset($paths) : null;
            if (! $first) {
                return null;
            }

            return Storage::disk('public')->url($first);
        });
    }

    /**
     * @return list<string>
     */
    public function gambarNotaUrls(): array
    {
        $paths = $this->gambar_nota ?? [];
        if (! is_array($paths)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $p): string => Storage::disk('public')->url($p),
            $paths
        )));
    }
}
