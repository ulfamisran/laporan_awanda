<?php

namespace App\Models;

use App\Enums\StatusPenggajian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggajian extends Model
{
    protected $table = 'penggajian';

    protected $fillable = [
        'relawan_id',
        'profil_mbg_id',
        'periode_id',
        'periode_bulan',
        'periode_tahun',
        'gaji_pokok',
        'tunjangan_transport',
        'tunjangan_makan',
        'tunjangan_lainnya',
        'potongan',
        'keterangan_potongan',
        'total_gaji',
        'tanggal_bayar',
        'status',
        'catatan',
        'created_by',
        'approved_by',
    ];

    protected $appends = [
        'periode_label',
    ];

    protected function casts(): array
    {
        return [
            'profil_mbg_id' => 'integer',
            'periode_id' => 'integer',
            'periode_bulan' => 'integer',
            'periode_tahun' => 'integer',
            'gaji_pokok' => 'decimal:2',
            'tunjangan_transport' => 'decimal:2',
            'tunjangan_makan' => 'decimal:2',
            'tunjangan_lainnya' => 'decimal:2',
            'potongan' => 'decimal:2',
            'total_gaji' => 'decimal:2',
            'tanggal_bayar' => 'date',
            'status' => StatusPenggajian::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Penggajian $model): void {
            $model->total_gaji = self::hitungTotal(
                (float) $model->gaji_pokok,
                (float) $model->tunjangan_transport,
                (float) $model->tunjangan_makan,
                (float) $model->tunjangan_lainnya,
                (float) $model->potongan,
            );
        });
    }

    public static function hitungTotal(
        float $gajiPokok,
        float $tunjTransport,
        float $tunjMakan,
        float $tunjLain,
        float $potongan,
    ): string {
        $total = $gajiPokok + $tunjTransport + $tunjMakan + $tunjLain - $potongan;

        return number_format(round($total, 2), 2, '.', '');
    }

    public function relawan(): BelongsTo
    {
        return $this->belongsTo(Relawan::class, 'relawan_id');
    }

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }

    public function periodeLaporan(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePeriode(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun);
    }

    public function getPeriodeLabelAttribute(): string
    {
        $bulan = (int) ($this->periode_bulan ?? 0);
        $tahun = (int) ($this->periode_tahun ?? 0);
        $nama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($nama[$bulan] ?? '—').' '.$tahun;
    }
}
