<?php

namespace App\Http\Controllers;

use App\Enums\BarangMasukSumber;
use App\Models\BarangMasuk;
use App\Models\OrderBarangItem;
use App\Models\ProfilMbg;
use App\Support\KodeTransaksiStok;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PenerimaanBarangController extends Controller
{
    public function index(): View
    {
        $profilId = ProfilMbgTenant::id();
        $periodeId = PeriodeTenant::id();

        $pendingItems = OrderBarangItem::query()
            ->with(['orderBarang', 'supplier'])
            ->whereHas('orderBarang', function ($q) use ($profilId, $periodeId): void {
                $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId);
            })
            ->withSum(['penerimaan as qty_diterima' => function ($q) use ($profilId, $periodeId): void {
                $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId);
            }], 'jumlah')
            ->orderByDesc('id')
            ->get()
            ->filter(function (OrderBarangItem $item): bool {
                $diterima = (float) ($item->qty_diterima ?? 0);

                return $diterima + 0.00001 < (float) $item->jumlah_barang;
            })
            ->values();

        $receivedRows = BarangMasuk::query()
            ->with(['orderItem.orderBarang', 'orderItem.supplier', 'barang', 'creator'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereNotNull('order_barang_item_id')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'diterima_page');

        return view('penerimaan-barang.index', compact('pendingItems', 'receivedRows'));
    }

    public function create(OrderBarangItem $item): View
    {
        $this->ensureOrderItemAccessible($item);
        $item->loadMissing(['orderBarang', 'supplier', 'barang']);

        $qtyDiterima = (float) BarangMasuk::query()
            ->where('order_barang_item_id', $item->id)
            ->where('profil_mbg_id', ProfilMbgTenant::id())
            ->where('periode_id', PeriodeTenant::id())
            ->sum('jumlah');

        $sisaQty = max((float) $item->jumlah_barang - $qtyDiterima, 0);
        if ($sisaQty <= 0) {
            return redirect()->route('stok.penerimaan.index')->with('success', 'Item order ini sudah diterima penuh.');
        }

        return view('penerimaan-barang.create', compact('item', 'qtyDiterima', 'sisaQty'));
    }

    public function store(Request $request, OrderBarangItem $item): RedirectResponse
    {
        $this->ensureOrderItemAccessible($item);
        $item->loadMissing('orderBarang');

        $qtyDiterima = (float) BarangMasuk::query()
            ->where('order_barang_item_id', $item->id)
            ->where('profil_mbg_id', ProfilMbgTenant::id())
            ->where('periode_id', PeriodeTenant::id())
            ->sum('jumlah');

        $sisaQty = max((float) $item->jumlah_barang - $qtyDiterima, 0);
        if ($sisaQty <= 0) {
            return redirect()->route('stok.penerimaan.index')->with('success', 'Item order ini sudah diterima penuh.');
        }

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'qty_diterima' => ['required', 'numeric', 'min:0.01', 'max:'.$sisaQty],
            'kondisi_penerimaan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'qty_diterima' => 'qty diterima',
            'kondisi_penerimaan' => 'kondisi',
        ]);

        $payload = [
            'kode_transaksi' => KodeTransaksiStok::generate('BM', 'barang_masuk'),
            'order_barang_item_id' => $item->id,
            'barang_id' => $item->barang_id,
            'profil_mbg_id' => ProfilMbgTenant::id(),
            'periode_id' => PeriodeTenant::id(),
            'tanggal' => $data['tanggal'],
            'jumlah' => round((float) $data['qty_diterima'], 2),
            'satuan' => $item->satuan_barang,
            'harga_satuan' => (float) $item->harga_barang,
            'sumber' => BarangMasukSumber::Pembelian->value,
            'kondisi_penerimaan' => (string) $data['kondisi_penerimaan'],
            'keterangan' => $data['keterangan'] ?? null,
            'created_by' => (int) $request->user()->getKey(),
        ];

        if ($request->hasFile('gambar')) {
            $payload['gambar'] = basename($request->file('gambar')->store('barang-masuk', 'public'));
        }

        BarangMasuk::query()->create($payload);

        return redirect()->route('stok.penerimaan.index')->with('success', 'Penerimaan barang berhasil disimpan dan masuk ke transaksi barang masuk.');
    }

    public function reportPdf()
    {
        $profilId = ProfilMbgTenant::id();
        $periodeId = PeriodeTenant::id();
        $profil = ProfilMbg::query()->find($profilId);
        $logoDataUri = $this->profilLogoDataUriForPdf($profil);

        $rows = OrderBarangItem::query()
            ->with(['orderBarang', 'supplier', 'barang'])
            ->whereHas('orderBarang', function ($q) use ($profilId, $periodeId): void {
                $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId);
            })
            ->withSum(['penerimaan as qty_diterima' => function ($q) use ($profilId, $periodeId): void {
                $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId);
            }], 'jumlah')
            ->withMax(['penerimaan as last_terima_at' => function ($q) use ($profilId, $periodeId): void {
                $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId);
            }], 'tanggal')
            ->orderBy('id')
            ->get()
            ->map(function (OrderBarangItem $item) use ($profilId, $periodeId) {
                $lastReceive = BarangMasuk::query()
                    ->where('order_barang_item_id', $item->id)
                    ->where('profil_mbg_id', $profilId)
                    ->where('periode_id', $periodeId)
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->first();

                return (object) [
                    'nomor_order' => $item->orderBarang?->nomor_order ?? '-',
                    'tanggal_order' => $item->orderBarang?->tanggal_order?->format('d/m/Y') ?? '-',
                    'barang' => $item->nama_barang,
                    'supplier' => $item->supplier?->nama_supplier ?? '-',
                    'qty_order' => number_format((float) $item->jumlah_barang, 2, ',', '.').' '.$item->satuan_barang,
                    'qty_diterima' => number_format((float) ($item->qty_diterima ?? 0), 2, ',', '.').' '.$item->satuan_barang,
                    'sisa' => number_format(max((float) $item->jumlah_barang - (float) ($item->qty_diterima ?? 0), 0), 2, ',', '.').' '.$item->satuan_barang,
                    'kondisi' => $lastReceive?->kondisi_penerimaan ?? '-',
                    'tanggal_terima' => $lastReceive?->tanggal?->format('d/m/Y') ?? '-',
                    'gambar_data_uri' => $this->barangMasukGambarDataUriForPdf($lastReceive?->gambar),
                ];
            });

        $pdf = Pdf::loadView('penerimaan-barang.report-pdf', [
            'rows' => $rows,
            'profil' => $profil,
            'logoDataUri' => $logoDataUri,
            'periodeAktif' => PeriodeTenant::model()->labelRingkas(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-penerimaan-barang.pdf');
    }

    private function ensureOrderItemAccessible(OrderBarangItem $item): void
    {
        $order = $item->orderBarang;
        if (! $order || (int) $order->profil_mbg_id !== ProfilMbgTenant::id() || (int) $order->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }

    private function profilLogoDataUriForPdf(?ProfilMbg $profil): ?string
    {
        if ($profil === null || $profil->logo === null || $profil->logo === '') {
            return null;
        }

        $relative = 'logo-mbg/'.$profil->logo;
        $disk = Storage::disk('public');
        if (! $disk->exists($relative)) {
            return null;
        }

        $path = $disk->path($relative);
        if (! is_readable($path)) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function barangMasukGambarDataUriForPdf(?string $nama): ?string
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
        if (! is_readable($path)) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }
}
