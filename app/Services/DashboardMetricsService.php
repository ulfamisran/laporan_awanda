<?php

namespace App\Services;

use App\Enums\SatuanLimbah;
use App\Enums\StatusAktif;
use App\Enums\StatusPenggajian;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\DanaKeluar;
use App\Models\DanaMasuk;
use App\Models\LaporanLimbah;
use App\Models\Penggajian;
use App\Models\ProfilMbg;
use App\Models\Relawan;
use App\Support\LimbahVolumeKg;
use App\Support\SaldoDana;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardMetricsService
{
    public function __construct(
        private readonly int $profilMbgId,
        private readonly int $periodeId
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'cards_row1' => $this->cardsRow1(),
            'cards_row2' => $this->cardsRow2BulanIni(),
            'charts' => [
                'dana_6m' => $this->chartDana6Bulan(),
                'barang_masuk_keluar_6m' => $this->chartBarangMasukKeluar6Bulan(),
                'dana_keluar_pie_bulan_ini' => $this->pieDanaKeluarKategoriBulanIni(),
                'top_barang_keluar' => $this->topBarangKeluarBulanIni(),
            ],
            'aktivitas' => $this->aktivitasTerbaru(10),
            'alert_stok_kritis' => $this->alertStokKritis(),
            'alert_penggajian' => $this->alertPenggajianBelumDibayar(),
        ];
    }

    /**
     * @return array{total_barang_aktif: int, stok_kritis: int, saldo_dana: float, relawan_aktif: int}
     */
    public function cardsRow1(): array
    {
        return [
            'total_barang_aktif' => (int) Barang::query()->where('status', StatusAktif::Aktif)->count(),
            'stok_kritis' => $this->countStokKritis(),
            'saldo_dana' => $this->saldoDanaAgg(),
            'relawan_aktif' => (int) Relawan::query()
                ->aktif()
                ->where('profil_mbg_id', $this->profilMbgId)
                ->count(),
        ];
    }

    /**
     * @return array{dana_masuk: float, dana_keluar: float, nilai_barang_masuk: float, limbah_kg: float}
     */
    public function cardsRow2BulanIni(): array
    {
        $mulai = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();

        $qDanaM = DanaMasuk::query()->whereBetween('tanggal', [$mulai, $akhir]);
        $qDanaK = DanaKeluar::query()->whereBetween('tanggal', [$mulai, $akhir]);
        $qBm = BarangMasuk::query()->whereBetween('tanggal', [$mulai, $akhir]);
        $qLb = LaporanLimbah::query()->whereBetween('tanggal', [$mulai, $akhir]);
        $this->applyProfil($qDanaM);
        $this->applyProfil($qDanaK);
        $this->applyProfil($qBm);
        $this->applyProfil($qLb);
        $this->applyPeriode($qDanaM);
        $this->applyPeriode($qDanaK);
        $this->applyPeriode($qBm);
        $this->applyPeriode($qLb);

        $limbahKg = (float) $qLb->get()->sum(function (LaporanLimbah $r) {
            $s = $r->satuan instanceof SatuanLimbah ? $r->satuan : SatuanLimbah::from((string) $r->satuan);

            return LimbahVolumeKg::estimate((float) $r->jumlah, $s);
        });

        return [
            'dana_masuk' => (float) $qDanaM->clone()->sum('jumlah'),
            'dana_keluar' => (float) $qDanaK->clone()->sum('jumlah'),
            'nilai_barang_masuk' => (float) $qBm->clone()->sum('total_harga'),
            'limbah_kg' => $limbahKg,
        ];
    }

    private function saldoDanaAgg(): float
    {
        return SaldoDana::getSaldoDana($this->profilMbgId);
    }

    private function countStokKritis(): int
    {
        return $this->alertStokKritis()->count();
    }

    /**
     * @return Collection<int, array{barang_id: int, nama_barang: string, profil_mbg_id: int|null, nama_dapur: string|null, stok: float, stok_minimum: float}>
     */
    public function alertStokKritis(): Collection
    {
        $pid = $this->profilMbgId;
        $out = collect();
        $barangs = Barang::query()->where('status', StatusAktif::Aktif)->get();
        $namaCabang = ProfilMbg::query()->whereKey($pid)->value('nama_dapur');

        foreach ($barangs as $b) {
            $stok = $b->getStokSaatIni($pid, $this->periodeId);
            $min = (float) $b->stok_minimum;
            if ($stok < $min) {
                $out->push([
                    'barang_id' => (int) $b->getKey(),
                    'nama_barang' => $b->nama_barang,
                    'profil_mbg_id' => $pid,
                    'nama_dapur' => $namaCabang,
                    'stok' => $stok,
                    'stok_minimum' => $min,
                ]);
            }
        }

        return $out->take(50)->values();
    }

    /**
     * @return Collection<int, array{nama_relawan: string, status_label: string, periode: string, dapur: string|null}>
     */
    public function alertPenggajianBelumDibayar(): Collection
    {
        $q = Penggajian::query()
            ->with(['relawan', 'profilMbg'])
            ->where('periode_bulan', now()->month)
            ->where('periode_tahun', now()->year)
            ->where('periode_id', $this->periodeId)
            ->whereIn('status', [StatusPenggajian::Draft, StatusPenggajian::Approved]);
        $this->applyProfil($q);

        return $q->orderBy('relawan_id')->limit(30)->get()->map(function (Penggajian $p): array {
            return [
                'nama_relawan' => $p->relawan?->nama_lengkap ?? '—',
                'status_label' => $p->status instanceof StatusPenggajian ? $p->status->label() : (string) $p->status,
                'periode' => $p->periode_label,
                'dapur' => $p->profilMbg?->nama_dapur,
            ];
        })->values();
    }

    /**
     * @return array{labels: list<string>, masuk: list<float>, keluar: list<float>}
     */
    public function chartDana6Bulan(): array
    {
        $labels = [];
        $masuk = [];
        $keluar = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->subMonths($i)->startOfMonth();
            $me = $m->copy()->endOfMonth();
            $labels[] = $m->translatedFormat('M Y');
            $qm = DanaMasuk::query()->whereBetween('tanggal', [$m->toDateString(), $me->toDateString()]);
            $qk = DanaKeluar::query()->whereBetween('tanggal', [$m->toDateString(), $me->toDateString()]);
            $this->applyProfil($qm);
            $this->applyProfil($qk);
            $this->applyPeriode($qm);
            $this->applyPeriode($qk);
            $masuk[] = round((float) $qm->sum('jumlah'), 2);
            $keluar[] = round((float) $qk->sum('jumlah'), 2);
        }

        return ['labels' => $labels, 'masuk' => $masuk, 'keluar' => $keluar];
    }

    /**
     * @return array{labels: list<string>, masuk: list<float>, keluar: list<float>}
     */
    public function chartBarangMasukKeluar6Bulan(): array
    {
        $labels = [];
        $masuk = [];
        $keluar = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->subMonths($i)->startOfMonth();
            $me = $m->copy()->endOfMonth();
            $labels[] = $m->translatedFormat('M Y');
            $qbm = BarangMasuk::query()->whereBetween('tanggal', [$m->toDateString(), $me->toDateString()]);
            $qbk = BarangKeluar::query()->whereBetween('tanggal', [$m->toDateString(), $me->toDateString()]);
            $this->applyProfil($qbm);
            $this->applyProfil($qbk);
            $this->applyPeriode($qbm);
            $this->applyPeriode($qbk);
            $masuk[] = round((float) $qbm->sum('jumlah'), 2);
            $keluar[] = round((float) $qbk->sum('jumlah'), 2);
        }

        return ['labels' => $labels, 'masuk' => $masuk, 'keluar' => $keluar];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function pieDanaKeluarKategoriBulanIni(): array
    {
        $mulai = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $rows = DanaKeluar::query()
            ->selectRaw('akun_jenis_dana_id, SUM(jumlah) as total')
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->groupBy('akun_jenis_dana_id');
        $this->applyProfil($rows);
        $this->applyPeriode($rows);
        $rows = $rows->with('akunJenisDana')->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $a = $row->akunJenisDana;
            $labels[] = $a ? ($a->kode.' — '.$a->nama) : '—';
            $values[] = round((float) $row->total, 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return list<array{nama: string, jumlah: float}>
     */
    public function topBarangKeluarBulanIni(): array
    {
        $mulai = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $q = BarangKeluar::query()
            ->selectRaw('barang_id, SUM(jumlah) as total')
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->groupBy('barang_id')
            ->orderByDesc('total')
            ->limit(5);
        $this->applyProfil($q);
        $rows = $q->with('barang')->get();

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'nama' => $row->barang?->nama_barang ?? '—',
                'jumlah' => round((float) $row->total, 2),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{waktu: string, jenis: string, jenis_label: string, deskripsi: string, nominal: string|null, jumlah: string|null, oleh: string|null, badge: string}>
     */
    public function aktivitasTerbaru(int $limit): array
    {
        $items = collect();

        $bm = BarangMasuk::query()->with('creator', 'barang', 'profilMbg');
        $this->applyProfil($bm);
        $this->applyPeriode($bm);
        foreach ($bm->orderByDesc('tanggal')->orderByDesc('id')->limit(15)->get() as $r) {
            $items->push([
                'ts' => ($r->tanggal?->timestamp ?? 0) + (int) $r->getKey() / 100000,
                'id' => (int) $r->getKey(),
                'waktu' => $r->tanggal?->format('d/m/Y') ?? '',
                'jenis' => 'barang_masuk',
                'jenis_label' => 'Barang masuk',
                'deskripsi' => ($r->barang?->nama_barang ?? 'Barang').' · '.($r->profilMbg?->nama_dapur ?? ''),
                'nominal' => formatRupiah($r->total_harga),
                'jumlah' => null,
                'oleh' => $r->creator?->name,
                'badge' => 'emerald',
            ]);
        }

        $bk = BarangKeluar::query()->with('creator', 'barang', 'profilMbg');
        $this->applyProfil($bk);
        $this->applyPeriode($bk);
        foreach ($bk->orderByDesc('tanggal')->orderByDesc('id')->limit(15)->get() as $r) {
            $items->push([
                'ts' => ($r->tanggal?->timestamp ?? 0) + (int) $r->getKey() / 100000,
                'id' => (int) $r->getKey(),
                'waktu' => $r->tanggal?->format('d/m/Y') ?? '',
                'jenis' => 'barang_keluar',
                'jenis_label' => 'Barang keluar',
                'deskripsi' => ($r->barang?->nama_barang ?? 'Barang').' · '.($r->profilMbg?->nama_dapur ?? ''),
                'nominal' => null,
                'jumlah' => number_format((float) $r->jumlah, 2, ',', '.').' '.$r->satuan,
                'oleh' => $r->creator?->name,
                'badge' => 'amber',
            ]);
        }

        $dm = DanaMasuk::query()->with('creator', 'akunJenisDana', 'akunKas', 'profilMbg');
        $this->applyProfil($dm);
        $this->applyPeriode($dm);
        foreach ($dm->orderByDesc('tanggal')->orderByDesc('id')->limit(15)->get() as $r) {
            $items->push([
                'ts' => ($r->tanggal?->timestamp ?? 0) + (int) $r->getKey() / 100000,
                'id' => (int) $r->getKey(),
                'waktu' => $r->tanggal?->format('d/m/Y') ?? '',
                'jenis' => 'dana_masuk',
                'jenis_label' => 'Dana masuk',
                'deskripsi' => $r->ringkasanBukuPembantu().' · '.($r->profilMbg?->nama_dapur ?? '')
                    .(trim((string) ($r->uraian_transaksi ?? '')) !== '' ? ' — '.Str::limit(trim((string) $r->uraian_transaksi), 120) : ''),
                'nominal' => formatRupiah($r->jumlah),
                'jumlah' => null,
                'oleh' => $r->creator?->name,
                'badge' => 'blue',
            ]);
        }

        $dk = DanaKeluar::query()->with('creator', 'akunJenisDana', 'akunKas', 'profilMbg');
        $this->applyProfil($dk);
        $this->applyPeriode($dk);
        foreach ($dk->orderByDesc('tanggal')->orderByDesc('id')->limit(15)->get() as $r) {
            $items->push([
                'ts' => ($r->tanggal?->timestamp ?? 0) + (int) $r->getKey() / 100000,
                'id' => (int) $r->getKey(),
                'waktu' => $r->tanggal?->format('d/m/Y') ?? '',
                'jenis' => 'dana_keluar',
                'jenis_label' => 'Dana keluar',
                'deskripsi' => $r->ringkasanBukuPembantu().' · '.($r->profilMbg?->nama_dapur ?? '')
                    .(trim((string) ($r->uraian_transaksi ?? '')) !== '' ? ' — '.Str::limit(trim((string) $r->uraian_transaksi), 120) : ''),
                'nominal' => formatRupiah($r->jumlah),
                'jumlah' => null,
                'oleh' => $r->creator?->name,
                'badge' => 'rose',
            ]);
        }

        return $items->sortByDesc(fn (array $a) => ($a['ts'] * 100000) + $a['id'])->take($limit)->values()->map(function (array $a) {
            unset($a['ts'], $a['id']);

            return $a;
        })->all();
    }

    private function applyProfil(Builder $q): void
    {
        $q->where('profil_mbg_id', $this->profilMbgId);
    }

    private function applyPeriode(Builder $q): void
    {
        $q->where('periode_id', $this->periodeId);
    }
}
