<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\KategoriBarang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index(): View
    {
        $kategoris = KategoriBarang::query()->orderBy('nama_kategori')->get();

        return view('barang.index', compact('kategoris'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = Barang::query()
            ->select('barang.*')
            ->with('kategoriBarang')
            ->selectRaw('(
                (SELECT COALESCE(SUM(jumlah),0) FROM stok_awal_barang WHERE stok_awal_barang.barang_id = barang.id)
                + (SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE barang_masuk.barang_id = barang.id)
                - (SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE barang_keluar.barang_id = barang.id)
            ) as stok_saat_ini_agg');

        if ($request->filled('kategori_barang_id')) {
            $query->where('barang.kategori_barang_id', $request->integer('kategori_barang_id'));
        }

        if ($request->filled('status_filter') && in_array($request->string('status_filter')->toString(), ['aktif', 'nonaktif'], true)) {
            $query->where('barang.status', $request->string('status_filter'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('foto_thumb', function (Barang $barang) {
                $url = $barang->foto_url;
                if (! $url) {
                    return '<span class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-[11px] font-bold text-white" style="background:#4a9b7a;">B</span>';
                }

                return '<img src="'.e($url).'" alt="" class="h-10 w-10 rounded-lg border object-cover" style="border-color:#d4e8f4;">';
            })
            ->addColumn('kategori_label', fn (Barang $barang) => e($barang->kategoriBarang?->nama_kategori ?? '—'))
            ->addColumn('satuan_label', fn (Barang $barang) => e($barang->satuan?->label() ?? '—'))
            ->addColumn('harga_label', fn (Barang $barang) => formatRupiah($barang->harga_satuan))
            ->addColumn('stok_cell', function (Barang $barang) {
                $stok = (float) ($barang->stok_saat_ini_agg ?? 0);
                $min = (float) $barang->stok_minimum;
                $warn = $stok < $min;
                $badge = $warn
                    ? '<span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase" style="background:#fde8e8;color:#c0392b;">Warning</span>'
                    : '';

                return '<span class="font-mono text-sm">'.e(number_format($stok, 2, ',', '.')).'</span>'.$badge;
            })
            ->addColumn('status_badge', function (Barang $barang) {
                if ($barang->status?->value === 'aktif') {
                    return '<span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#d4f0e8;color:#2d7a60;">Aktif</span>';
                }

                return '<span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#fde8e8;color:#c0392b;">Nonaktif</span>';
            })
            ->addColumn('aksi', function (Barang $barang) {
                $detail = '<a href="'.e(route('master.barang.show', $barang)).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                $edit = '';
                if (auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat', 'admin'])) {
                    $edit = '<a href="'.e(route('master.barang.edit', $barang)).'" class="ml-3 text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>';
                }
                $hapus = '';
                if (auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
                    $hapus = '<form method="POST" action="'.e(route('master.barang.destroy', $barang)).'" class="ml-3 inline form-hapus-barang">'
                        .csrf_field()
                        .method_field('DELETE')
                        .'<button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>'
                        .'</form>';
                }

                return '<div class="flex flex-wrap items-center justify-end gap-y-1">'.$detail.$edit.$hapus.'</div>';
            })
            ->rawColumns(['foto_thumb', 'stok_cell', 'status_badge', 'aksi'])
            ->toJson();
    }

    public function create(): View
    {
        $this->authorizeBarangWrite();

        $kategoris = KategoriBarang::query()->orderBy('nama_kategori')->get();
        $nextKodePreview = Barang::previewNextKode();

        return view('barang.create', compact('kategoris', 'nextKodePreview'));
    }

    public function store(StoreBarangRequest $request): RedirectResponse
    {
        $this->authorizeBarangWrite();

        $data = $request->validated();
        unset($data['foto']);

        if ($request->hasFile('foto')) {
            $data['foto'] = basename($request->file('foto')->store('foto-barang', 'public'));
        }

        Barang::query()->create($data);

        return redirect()
            ->route('master.barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Barang $barang): View
    {
        $barang->load('kategoriBarang');

        $chartLabels = [];
        $chartMasuk = [];
        $chartKeluar = [];

        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $chartLabels[] = $d->format('d/m');

            $chartMasuk[] = (float) BarangMasuk::query()
                ->where('barang_id', $barang->id)
                ->whereDate('tanggal', $d->toDateString())
                ->sum('jumlah');

            $chartKeluar[] = (float) BarangKeluar::query()
                ->where('barang_id', $barang->id)
                ->whereDate('tanggal', $d->toDateString())
                ->sum('jumlah');
        }

        $masuk = BarangMasuk::query()
            ->where('barang_id', $barang->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $keluar = BarangKeluar::query()
            ->where('barang_id', $barang->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $riwayat = collect()
            ->merge($masuk->map(fn (BarangMasuk $r) => [
                'tipe' => 'masuk',
                'tanggal' => $r->tanggal,
                'jumlah' => (float) $r->jumlah,
                'keterangan' => $r->keterangan,
            ]))
            ->merge($keluar->map(fn (BarangKeluar $r) => [
                'tipe' => 'keluar',
                'tanggal' => $r->tanggal,
                'jumlah' => (float) $r->jumlah,
                'keterangan' => $r->keterangan,
            ]))
            ->sortByDesc(fn (array $r) => $r['tanggal'] instanceof Carbon ? $r['tanggal']->timestamp : 0)
            ->values()
            ->take(30);

        return view('barang.show', compact('barang', 'chartLabels', 'chartMasuk', 'chartKeluar', 'riwayat'));
    }

    public function edit(Barang $barang): View
    {
        $this->authorizeBarangWrite();

        $kategoris = KategoriBarang::query()->orderBy('nama_kategori')->get();

        return view('barang.edit', compact('barang', 'kategoris'));
    }

    public function update(UpdateBarangRequest $request, Barang $barang): RedirectResponse
    {
        $this->authorizeBarangWrite();

        $data = $request->validated();
        unset($data['foto']);

        if ($request->hasFile('foto')) {
            if ($barang->foto) {
                Storage::disk('public')->delete('foto-barang/'.$barang->foto);
            }
            $data['foto'] = basename($request->file('foto')->store('foto-barang', 'public'));
        }

        $barang->update($data);

        return redirect()
            ->route('master.barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Request $request, Barang $barang): RedirectResponse
    {
        if (! $request->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat menghapus barang.');
        }

        if ($barang->foto) {
            Storage::disk('public')->delete('foto-barang/'.$barang->foto);
        }

        $barang->delete();

        return redirect()
            ->route('master.barang.index')
            ->with('success', 'Barang berhasil dihapus (soft delete).');
    }

    private function authorizeBarangWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data barang.');
        }
    }
}
