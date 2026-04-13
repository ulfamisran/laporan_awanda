<?php

namespace App\Http\Controllers;

use App\Enums\BarangMasukSumber;
use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\OrderBarangItem;
use App\Support\KodeTransaksiStok;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class BarangMasukController extends Controller
{
    use Concerns\ManagesStokProfil;

    public function index(Request $request): View
    {
        $profilIdDefault = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();

        return view('barang-masuk.index', compact('profilIdDefault', 'periodeId'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = BarangMasuk::query()
            ->with(['barang.kategoriBarang', 'profilMbg', 'creator'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $query->where('profil_mbg_id', ProfilMbgTenant::id());
        $query->where('periode_id', PeriodeTenant::id());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal', fn (BarangMasuk $r) => $r->tanggal?->format('d/m/Y') ?? '')
            ->addColumn('barang_cell', fn (BarangMasuk $r) => e($r->barang?->kode_barang.' — '.$r->barang?->nama_barang))
            ->addColumn('dapur_cell', fn (BarangMasuk $r) => e($r->profilMbg?->nama_dapur ?? '—'))
            ->addColumn('jumlah_cell', fn (BarangMasuk $r) => '<span class="font-mono">'.e(number_format((float) $r->jumlah, 2, ',', '.')).' '.e($r->satuan).'</span>')
            ->addColumn('total_cell', fn (BarangMasuk $r) => formatRupiah($r->total_harga))
            ->addColumn('sumber_label', fn (BarangMasuk $r) => e($r->sumber?->label() ?? '—'))
            ->addColumn('aksi', function (BarangMasuk $r) {
                $show = '<a href="'.e(route('stok.masuk.show', $r)).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                $edit = '<a href="'.e(route('stok.masuk.edit', $r)).'" class="ml-3 text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>';
                $hapus = '';
                if ($this->userCanDeleteStokRecord($r)) {
                    $hapus = '<form method="POST" action="'.e(route('stok.masuk.destroy', $r)).'" class="ml-3 inline form-hapus-stok">'
                        .csrf_field()
                        .method_field('DELETE')
                        .'<button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>'
                        .'</form>';
                }

                return '<div class="flex flex-wrap items-center justify-end">'.$show.$edit.$hapus.'</div>';
            })
            ->rawColumns(['jumlah_cell', 'aksi'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $barangs = Barang::query()->where('status', StatusAktif::Aktif)->orderBy('nama_barang')->get();
        $orderItems = OrderBarangItem::query()
            ->with(['orderBarang', 'supplier', 'barang'])
            ->whereHas('orderBarang', function ($q) use ($profilId, $periodeId): void {
                $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId);
            })
            ->orderByDesc('id')
            ->get();
        $previewKode = $this->previewNextKode();

        return view('barang-masuk.create', compact('profilId', 'periodeId', 'barangs', 'previewKode', 'orderItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromStokForm($request);
        $this->mergeHargaMasukFromRequest($request);

        $data = $this->validatedMasuk($request);
        if (! empty($data['order_barang_item_id'])) {
            $orderItem = OrderBarangItem::query()->with('orderBarang')->find((int) $data['order_barang_item_id']);
            if (! $orderItem || ! $orderItem->orderBarang) {
                abort(422, 'Item order barang tidak ditemukan.');
            }
            if ((int) $orderItem->orderBarang->profil_mbg_id !== (int) $profilId || (int) $orderItem->orderBarang->periode_id !== PeriodeTenant::id()) {
                abort(422, 'Item order barang tidak sesuai cabang/periode aktif.');
            }
            $data['barang_id'] = (int) $orderItem->barang_id;
            $data['satuan'] = (string) $orderItem->satuan_barang;
        }
        $data['profil_mbg_id'] = $profilId;
        $data['periode_id'] = PeriodeTenant::id();
        $data['created_by'] = (int) $request->user()->getKey();
        $data['kode_transaksi'] = KodeTransaksiStok::generate('BM', 'barang_masuk');

        unset($data['gambar']);
        if ($request->hasFile('gambar')) {
            $data['gambar'] = basename($request->file('gambar')->store('barang-masuk', 'public'));
        }

        BarangMasuk::query()->create($data);

        return redirect()
            ->route('stok.masuk.index')
            ->with('success', 'Transaksi barang masuk berhasil disimpan.');
    }

    public function show(Request $request, BarangMasuk $masuk): View
    {
        $this->ensureProfilPeriodeRow($request, $masuk);
        $masuk->load(['barang.kategoriBarang', 'profilMbg', 'creator', 'orderItem.orderBarang', 'orderItem.supplier']);

        return view('barang-masuk.show', compact('masuk'));
    }

    public function edit(Request $request, BarangMasuk $masuk): View
    {
        $this->ensureProfilPeriodeRow($request, $masuk);
        $profilId = $masuk->profil_mbg_id;
        $masuk->load('barang');
        $previewKode = $masuk->kode_transaksi;
        $periodeId = PeriodeTenant::id();

        return view('barang-masuk.edit', compact('masuk', 'profilId', 'periodeId', 'previewKode'));
    }

    public function orderItemApi(Request $request, OrderBarangItem $item): JsonResponse
    {
        $item->loadMissing('orderBarang', 'supplier');
        $order = $item->orderBarang;
        if (! $order
            || (int) $order->profil_mbg_id !== ProfilMbgTenant::id()
            || (int) $order->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }

        return response()->json([
            'id' => $item->id,
            'barang_id' => $item->barang_id,
            'nama_barang' => $item->nama_barang,
            'satuan_barang' => $item->satuan_barang,
            'harga_barang' => (float) $item->harga_barang,
            'jumlah_barang' => (float) $item->jumlah_barang,
            'supplier' => $item->supplier?->nama_supplier,
            'nomor_order' => $order->nomor_order,
        ]);
    }

    public function update(Request $request, BarangMasuk $masuk): RedirectResponse
    {
        $this->ensureProfilPeriodeRow($request, $masuk);
        $this->mergeHargaMasukFromRequest($request);

        $data = $this->validatedMasuk($request, isUpdate: true);
        unset($data['gambar'], $data['barang_id']);

        if ($request->hasFile('gambar')) {
            if ($masuk->gambar) {
                Storage::disk('public')->delete('barang-masuk/'.$masuk->gambar);
            }
            $data['gambar'] = basename($request->file('gambar')->store('barang-masuk', 'public'));
        }

        $masuk->update($data);

        return redirect()
            ->route('stok.masuk.index')
            ->with('success', 'Transaksi barang masuk diperbarui.');
    }

    public function destroy(Request $request, BarangMasuk $masuk): RedirectResponse
    {
        $this->ensureProfilPeriodeRow($request, $masuk);

        if (! $this->userCanDeleteStokRecord($masuk)) {
            abort(403, 'Anda tidak dapat menghapus transaksi ini.');
        }

        if ($masuk->gambar) {
            Storage::disk('public')->delete('barang-masuk/'.$masuk->gambar);
        }

        $masuk->delete();

        return redirect()
            ->route('stok.masuk.index')
            ->with('success', 'Transaksi barang masuk dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->exportMasukRows();
        $pdfRows = $rows->map(function (BarangMasuk $r) {
            $b = $r->barang;

            return (object) [
                'tanggal_fmt' => $r->tanggal?->format('d/m/Y') ?? '—',
                'kategori' => $b?->kategoriBarang?->nama_kategori ?? '—',
                'nama_barang' => $b ? ($b->kode_barang.' — '.$b->nama_barang) : '—',
                'jumlah_label' => number_format((float) $r->jumlah, 2, ',', '.').' '.$r->satuan,
                'gambar_data_uri' => $this->barangMasukGambarDataUriForPdf($r->gambar),
            ];
        });

        $pdf = Pdf::loadView('barang-masuk.list-pdf', ['pdfRows' => $pdfRows])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-barang-masuk-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportWord(Request $request): BinaryFileResponse
    {
        $rows = $this->exportMasukRows();

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addText('Laporan barang masuk', ['bold' => true, 'size' => 16, 'color' => '1A4A6B']);
        $section->addText(
            'Diurutkan berdasarkan tanggal. Dicetak: '.now()->format('d/m/Y H:i'),
            ['size' => 9, 'color' => '4A6B7F']
        );

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'D4E8F4',
            'cellMargin' => 80,
        ]);

        $table->addRow();
        foreach (['Tanggal', 'Kategori barang', 'Nama barang', 'Jumlah', 'Gambar'] as $header) {
            $table->addCell()->addText($header, ['bold' => true, 'size' => 9, 'color' => '1A4A6B']);
        }

        foreach ($rows as $r) {
            $b = $r->barang;
            $table->addRow();
            $table->addCell()->addText($r->tanggal?->format('d/m/Y') ?? '—', ['size' => 9]);
            $table->addCell()->addText($b?->kategoriBarang?->nama_kategori ?? '—', ['size' => 9]);
            $table->addCell()->addText($b ? ($b->kode_barang.' — '.$b->nama_barang) : '—', ['size' => 9]);
            $table->addCell()->addText(
                number_format((float) $r->jumlah, 2, ',', '.').' '.$r->satuan,
                ['size' => 9]
            );

            $imgCell = $table->addCell();
            $path = $this->barangMasukGambarDiskPath($r->gambar);
            if ($path !== null) {
                try {
                    $imgCell->addImage($path, ['width' => 110, 'height' => 82]);
                } catch (\Throwable) {
                    $imgCell->addText('—', ['size' => 9, 'color' => '94A3B8']);
                }
            } else {
                $imgCell->addText('—', ['size' => 9, 'color' => '94A3B8']);
            }
        }

        $fileName = 'laporan-barang-masuk-'.now()->format('Ymd-His').'.docx';
        $tempPath = tempnam(sys_get_temp_dir(), 'bmx');
        if ($tempPath === false) {
            abort(500, 'Gagal menyiapkan file.');
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * @return Collection<int, BarangMasuk>
     */
    private function exportMasukRows(): Collection
    {
        return BarangMasuk::query()
            ->with(['barang.kategoriBarang'])
            ->where('profil_mbg_id', ProfilMbgTenant::id())
            ->where('periode_id', PeriodeTenant::id())
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
    }

    private function barangMasukGambarDiskPath(?string $nama): ?string
    {
        if ($nama === null || $nama === '') {
            return null;
        }

        $relative = 'barang-masuk/'.$nama;
        $disk = Storage::disk('public');
        if (! $disk->exists($relative)) {
            return null;
        }

        $path = $disk->path($relative);

        return is_readable($path) ? $path : null;
    }

    private function barangMasukGambarDataUriForPdf(?string $nama): ?string
    {
        $path = $this->barangMasukGambarDiskPath($nama);
        if ($path === null) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function validatedMasuk(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'barang_id' => [$isUpdate ? 'sometimes' : 'required_without:order_barang_item_id', 'nullable', 'integer', 'exists:barang,id'],
            'order_barang_item_id' => ['nullable', 'integer', 'exists:order_barang_items,id'],
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'satuan' => ['required', 'string', 'max:32'],
            'harga_satuan' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'sumber' => ['required', 'string', Rule::in(array_map(static fn (BarangMasukSumber $e) => $e->value, BarangMasukSumber::cases()))],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'kondisi_penerimaan' => ['nullable', 'string', 'max:255'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        $data = $request->validate($rules, [], [
            'barang_id' => 'barang',
            'harga_satuan' => 'harga satuan',
        ]);

        return $data;
    }

    private function mergeHargaMasukFromRequest(Request $request): void
    {
        if ($request->has('harga_satuan')) {
            $digits = preg_replace('/\D+/', '', (string) $request->input('harga_satuan'));
            $request->merge(['harga_satuan' => $digits === '' ? '0' : $digits]);
        }
    }

    private function previewNextKode(): string
    {
        $prefix = 'BM-'.now()->format('Ymd').'-';
        $last = BarangMasuk::query()
            ->where('kode_transaksi', 'like', $prefix.'%')
            ->orderByDesc('kode_transaksi')
            ->value('kode_transaksi');
        $next = 1;
        if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function ensureProfilRow(Request $request, int $profilMbgId): void
    {
        $scoped = $request->attributes->get('scoped_profil_mbg_id');
        if ($scoped !== null && $scoped !== '' && (int) $scoped !== (int) $profilMbgId) {
            abort(403, 'Transaksi ini berada di luar dapur Anda.');
        }
    }

    private function ensureProfilPeriodeRow(Request $request, BarangMasuk $masuk): void
    {
        $this->ensureProfilRow($request, $masuk->profil_mbg_id);
        if ((int) $masuk->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }
}
