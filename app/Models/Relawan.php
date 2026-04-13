<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Relawan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'posisi_relawan_id',
        'profil_mbg_id',
        'jenis_kelamin',
        'no_hp',
        'email',
        'alamat',
        'tanggal_lahir',
        'tanggal_bergabung',
        'foto',
        'gaji_pokok',
        'gaji_per_hari',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'profil_mbg_id' => 'integer',
            'posisi_relawan_id' => 'integer',
            'tanggal_lahir' => 'date',
            'tanggal_bergabung' => 'date',
            'gaji_pokok' => 'decimal:2',
            'gaji_per_hari' => 'decimal:2',
        ];
    }

    public function posisiRelawan(): BelongsTo
    {
        return $this->belongsTo(PosisiRelawan::class, 'posisi_relawan_id');
    }

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }

    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'relawan_id');
    }

    protected function fotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->foto) {
                return null;
            }

            return Storage::disk('public')->url('foto-relawan/'.$this->foto);
        });
    }

    protected function umur(): Attribute
    {
        return Attribute::get(function (): ?int {
            if (! $this->tanggal_lahir) {
                return null;
            }

            return Carbon::parse($this->tanggal_lahir)->age;
        });
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByDapur(Builder $query, int $profilMbgId): Builder
    {
        return $query->where('profil_mbg_id', $profilMbgId);
    }
}
