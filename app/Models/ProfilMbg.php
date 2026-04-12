<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProfilMbg extends Model
{
    use SoftDeletes;

    protected $table = 'profil_mbg';

    protected $fillable = [
        'nama_dapur',
        'kode_dapur',
        'id_sppg',
        'alamat',
        'kota',
        'provinsi',
        'no_telp',
        'penanggung_jawab',
        'nama_akuntansi',
        'nama_ahli_gizi',
        'nama_yayasan',
        'ketua_yayasan',
        'nomor_rekening_va',
        'tahun_anggaran',
        'tempat_pelaporan',
        'logo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'tahun_anggaran' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'profil_mbg_id');
    }

    public function relawans(): HasMany
    {
        return $this->hasMany(Relawan::class, 'profil_mbg_id');
    }

    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'profil_mbg_id');
    }

    public function laporanLimbah(): HasMany
    {
        return $this->hasMany(LaporanLimbah::class, 'profil_mbg_id');
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->logo) {
                return null;
            }

            return Storage::disk('public')->url('logo-mbg/'.$this->logo);
        });
    }
}
