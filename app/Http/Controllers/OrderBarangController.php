<?php

namespace App\Http\Controllers;

use App\Enums\SatuanBarang;
use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\OrderBarang;
use App\Models\ProfilMbg;
use App\Models\Supplier;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderBarangController extends Controller
{
    public function index(Request $request): View
    {
        $items = OrderBarang::query()
            ->with('creator')
            ->withCount('items')
            ->where('profil_mbg_id', ProfilMbgTenant::id())
            ->where('periode_id', PeriodeTenant::id())
            ->orderByDesc('tanggal_order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('order-barang.index', compact('items'));
    }

    public function create(): View
    {
        return view('order-barang.create', [
            'isEdit' => false,
            'pageTitle' => 'Buat Order Barang',
            'heading' => 'Buat order barang',
            'formAction' => route('stok.order.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Simpan order',
            'previewNomorOrder' => $this->previewNextNomorOrder(),
            'initialItems' => old('items', []),
            'tanggalOrder' => old('tanggal_order', now()->toDateString()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($request, $data): void {
            $order = OrderBarang::query()->create([
                'nomor_order' => $this->generateNomorOrder(),
                'profil_mbg_id' => ProfilMbgTenant::id(),
                'periode_id' => PeriodeTenant::id(),
                'tanggal_order' => $data['tanggal_order'],
                'created_by' => (int) $request->user()->getKey(),
            ]);

            $this->syncOrderItems($order, $data['items']);
        });

        return redirect()
            ->route('stok.order.index')
            ->with('success', 'Order barang berhasil disimpan.');
    }

    public function edit(OrderBarang $order): View
    {
        $this->ensureAccessible($order);
        $order->load('items.supplier');

        $initialItems = old('items', $order->items->map(function ($item): array {
            return [
                'nama_barang' => $item->nama_barang,
                'harga_barang' => number_format((float) $item->harga_barang, 0, '', ''),
                'jumlah_barang' => number_format((float) $item->jumlah_barang, 2, '.', ''),
                'satuan_barang' => $item->satuan_barang,
                'supplier_nama' => $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '',
                'jumlah_hari_pemakaian' => (int) $item->jumlah_hari_pemakaian,
            ];
        })->values()->all());

        return view('order-barang.create', [
            'isEdit' => true,
            'pageTitle' => 'Update Order Barang',
            'heading' => 'Update order barang',
            'formAction' => route('stok.order.update', $order),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update order',
            'previewNomorOrder' => $order->nomor_order,
            'initialItems' => $initialItems,
            'tanggalOrder' => old('tanggal_order', optional($order->tanggal_order)->toDateString()),
        ]);
    }

    public function update(Request $request, OrderBarang $order): RedirectResponse
    {
        $this->ensureAccessible($order);
        $data = $this->validatePayload($request);

        if ($order->items()->whereHas('penerimaan')->exists()) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Order ini sudah dipakai pada penerimaan barang, sehingga tidak bisa diubah.']);
        }

        DB::transaction(function () use ($order, $data): void {
            $order->update([
                'tanggal_order' => $data['tanggal_order'],
            ]);

            $order->items()->delete();
            $this->syncOrderItems($order, $data['items']);
        });

        return redirect()
            ->route('stok.order.index')
            ->with('success', 'Order barang berhasil diperbarui.');
    }

    public function show(OrderBarang $order): View
    {
        $this->ensureAccessible($order);
        $order->load(['items.barang', 'items.supplier', 'profilMbg']);

        return view('order-barang.show', compact('order'));
    }

    public function cetakNota(OrderBarang $order)
    {
        $this->ensureAccessible($order);
        $order->load(['items.supplier', 'profilMbg']);
        $profil = ProfilMbg::query()->find(ProfilMbgTenant::id());
        $logoDataUri = $this->profilLogoDataUriForPdf($profil);

        $pdf = Pdf::loadView('order-barang.nota-pdf', [
            'order' => $order,
            'profil' => $profil,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait');

        $safeNomorOrder = str_replace(['/', '\\'], '-', (string) $order->nomor_order);

        return $pdf->stream('nota-order-'.$safeNomorOrder.'.pdf');
    }

    private function ensureAccessible(OrderBarang $order): void
    {
        if ((int) $order->profil_mbg_id !== ProfilMbgTenant::id() || (int) $order->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }

    private function previewNextNomorOrder(): string
    {
        $suffix = '/SPPG/PL/'.now()->format('m/Y');
        $last = OrderBarang::query()
            ->where('nomor_order', 'like', '%'.$suffix)
            ->orderByDesc('nomor_order')
            ->value('nomor_order');
        $next = 1;
        if ($last && preg_match('/^(\d{3})\/SPPG\/PL\/\d{2}\/\d{4}$/', (string) $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return str_pad((string) $next, 3, '0', STR_PAD_LEFT).$suffix;
    }

    private function generateNomorOrder(): string
    {
        return DB::transaction(function (): string {
            $suffix = '/SPPG/PL/'.now()->format('m/Y');
            $last = OrderBarang::query()
                ->where('nomor_order', 'like', '%'.$suffix)
                ->lockForUpdate()
                ->orderByDesc('nomor_order')
                ->value('nomor_order');

            $next = 1;
            if ($last && preg_match('/^(\d{3})\/SPPG\/PL\/\d{2}\/\d{4}$/', (string) $last, $m)) {
                $next = (int) $m[1] + 1;
            }

            return str_pad((string) $next, 3, '0', STR_PAD_LEFT).$suffix;
        });
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'tanggal_order' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_barang' => ['required', 'string', 'max:255'],
            'items.*.harga_barang' => ['required', 'numeric', 'min:0'],
            'items.*.jumlah_barang' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan_barang' => ['required', 'string', 'max:32'],
            'items.*.supplier_nama' => ['nullable', 'string', 'max:255'],
            'items.*.jumlah_hari_pemakaian' => ['required', 'integer', 'min:0', 'max:3650'],
        ], [], [
            'tanggal_order' => 'tanggal order',
            'items.*.nama_barang' => 'nama barang',
            'items.*.harga_barang' => 'harga barang',
            'items.*.jumlah_barang' => 'jumlah barang',
            'items.*.satuan_barang' => 'satuan barang',
            'items.*.supplier_nama' => 'nama supplier',
            'items.*.jumlah_hari_pemakaian' => 'pemakaian (hari)',
        ]);
    }

    private function syncOrderItems(OrderBarang $order, array $rows): void
    {
        foreach ($rows as $row) {
            $barang = $this->resolveBarang((string) $row['nama_barang'], (float) $row['harga_barang']);
            $supplier = $this->resolveSupplier($row['supplier_nama'] ?? null);

            $order->items()->create([
                'barang_id' => (int) $barang->getKey(),
                'supplier_id' => $supplier?->getKey(),
                'nama_barang' => $barang->nama_barang,
                'supplier_nama' => $supplier?->nama_supplier ?? null,
                'harga_barang' => (float) $row['harga_barang'],
                'jumlah_barang' => (float) $row['jumlah_barang'],
                'satuan_barang' => (string) $row['satuan_barang'],
                'jumlah_hari_pemakaian' => (int) ($row['jumlah_hari_pemakaian'] ?? 0),
            ]);
        }
    }

    private function resolveBarang(string $namaBarang, float $hargaBarang): Barang
    {
        $nama = trim($namaBarang);

        $existing = Barang::query()
            ->whereRaw('LOWER(nama_barang) = ?', [mb_strtolower($nama)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $kategoriDefault = KategoriBarang::query()->firstOrCreate(
            ['nama_kategori' => 'Umum'],
            ['deskripsi' => 'Kategori default dari input order barang.']
        );

        return Barang::query()->create([
            'nama_barang' => $nama,
            'kategori_barang_id' => (int) $kategoriDefault->getKey(),
            // Satuan master barang harus mengikuti enum; satuan bebas tetap disimpan di order item.
            'satuan' => SatuanBarang::Lainnya->value,
            'harga_satuan' => $hargaBarang,
            'stok_minimum' => 0,
            'status' => StatusAktif::Aktif->value,
        ]);
    }

    private function resolveSupplier(?string $supplierNama): ?Supplier
    {
        $nama = trim((string) $supplierNama);
        if ($nama === '') {
            return null;
        }

        $existing = Supplier::query()
            ->whereRaw('LOWER(nama_supplier) = ?', [mb_strtolower($nama)])
            ->first();

        if ($existing) {
            return $existing;
        }

        return Supplier::query()->create([
            'nama_supplier' => $nama,
            'no_hp' => '-',
            'alamat' => '-',
        ]);
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
}
