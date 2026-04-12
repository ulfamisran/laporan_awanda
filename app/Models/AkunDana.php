<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkunDana extends Model
{
    /** Grup induk Buku Pembantu Kas (anak daun: kas kecil, bank, …). */
    public const KODE_GRUP_BUKU_PEMBANTU_KAS = '1100';

    /** Grup induk Buku Pembantu Jenis Dana. */
    public const KODE_GRUP_BUKU_PEMBANTU_JENIS_DANA = '2000';

    protected $table = 'akun_dana';

    protected $fillable = [
        'kode',
        'nama',
        'parent_id',
        'urutan',
        'is_grup',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'urutan' => 'integer',
            'is_grup' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan')->orderBy('kode');
    }

    public function stokDanaAwalAkun(): HasMany
    {
        return $this->hasMany(StokDanaAwalAkun::class, 'akun_dana_id');
    }

    public static function idGrupBukuPembantuKas(): ?int
    {
        $id = self::query()
            ->where('kode', self::KODE_GRUP_BUKU_PEMBANTU_KAS)
            ->where('is_grup', true)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    public static function idGrupBukuPembantuJenisDana(): ?int
    {
        $id = self::query()
            ->where('kode', self::KODE_GRUP_BUKU_PEMBANTU_JENIS_DANA)
            ->where('is_grup', true)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Akun daun di bawah grup Buku Pembantu Jenis Dana (untuk pilihan transaksi).
     *
     * @return EloquentCollection<int, AkunDana>
     */
    public static function daftarAkunBukuPembantuJenisDana(): EloquentCollection
    {
        $pid = self::idGrupBukuPembantuJenisDana();
        if ($pid === null) {
            return new EloquentCollection;
        }

        return self::query()
            ->where('parent_id', $pid)
            ->where('is_grup', false)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();
    }

    /**
     * Akun daun di bawah grup Buku Pembantu Kas.
     *
     * @return EloquentCollection<int, AkunDana>
     */
    public static function daftarAkunBukuPembantuKas(): EloquentCollection
    {
        $pid = self::idGrupBukuPembantuKas();
        if ($pid === null) {
            return new EloquentCollection;
        }

        return self::query()
            ->where('parent_id', $pid)
            ->where('is_grup', false)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();
    }

    /**
     * @return list<int>
     */
    public static function idsAkunBukuPembantuJenisDana(): array
    {
        return self::daftarAkunBukuPembantuJenisDana()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return list<int>
     */
    public static function idsAkunBukuPembantuKas(): array
    {
        return self::daftarAkunBukuPembantuKas()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeUntukPilihanKeuangan(Builder $query): Builder
    {
        return $query->where('is_grup', false)->orderBy('urutan')->orderBy('kode');
    }
}
