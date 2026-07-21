<?php

namespace App\Http\Controllers;

use App\Enums\SatuanBarang;
use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\OrderBarang;
use App\Models\OrderBarangItem;
use App\Models\ProfilMbg;
use App\Models\Supplier;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            'suppliers' => Supplier::query()->orderBy('nama_supplier')->get(['id', 'nama_supplier']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($request, $data): void {
            $supplierKeys = collect($data['items'])
                ->map(fn (array $row): string => $this->supplierGroupKey($row['supplier_nama'] ?? null))
                ->unique()
                ->sort()
                ->values();

            $nomorList = $this->allocateSequentialNomor($supplierKeys->count());
            $notaByKey = $supplierKeys->combine($nomorList)->all();

            $order = OrderBarang::query()->create([
                'nomor_order' => $nomorList[0],
                'profil_mbg_id' => ProfilMbgTenant::id(),
                'periode_id' => PeriodeTenant::id(),
                'tanggal_order' => $data['tanggal_order'],
                'created_by' => (int) $request->user()->getKey(),
            ]);

            $this->syncOrderItems($order, $data['items'], $notaByKey);
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
            'suppliers' => Supplier::query()->orderBy('nama_supplier')->get(['id', 'nama_supplier']),
        ]);
    }

    public function update(Request $request, OrderBarang $order): RedirectResponse
    {
        $this->ensureAccessible($order);
        $data = $this->validatePayload($request, $order);

        if ($order->items()->whereHas('penerimaan')->exists()) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Order ini sudah dipakai pada penerimaan barang, sehingga tidak bisa diubah.']);
        }

        DB::transaction(function () use ($order, $data): void {
            $preservedNotaByKey = [];
            foreach ($order->items as $item) {
                $key = $this->supplierGroupKey($item->supplier_nama ?? $item->supplier?->nama_supplier);
                if (! empty($item->nomor_nota) && ! isset($preservedNotaByKey[$key])) {
                    $preservedNotaByKey[$key] = (string) $item->nomor_nota;
                }
            }

            $order->update([
                'tanggal_order' => $data['tanggal_order'],
                'nomor_order' => $data['nomor_order'],
            ]);

            $order->items()->delete();
            $this->syncOrderItems($order, $data['items'], $preservedNotaByKey);
        });

        return redirect()
            ->route('stok.order.index')
            ->with('success', 'Order barang berhasil diperbarui.');
    }

    public function destroy(OrderBarang $order): RedirectResponse
    {
        $this->ensureAccessible($order);

        if ($order->items()->whereHas('penerimaan')->exists()) {
            return redirect()
                ->route('stok.order.index')
                ->withErrors(['order' => 'Order ini sudah dipakai pada penerimaan barang, sehingga tidak bisa dihapus.']);
        }

        DB::transaction(function () use ($order): void {
            $order->items()->delete();
            $order->delete();
        });

        return redirect()
            ->route('stok.order.index')
            ->with('success', 'Order barang berhasil dihapus.');
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

    public function cetakNotaSupplier(OrderBarang $order)
    {
        $this->ensureAccessible($order);
        $order->load(['items.supplier', 'profilMbg']);
        $this->ensureNomorNotaAssigned($order);
        $order->load(['items.supplier', 'profilMbg']);

        $profil = ProfilMbg::query()->find(ProfilMbgTenant::id());
        $logoDataUri = $this->profilLogoDataUriForPdf($profil);
        $supplierGroups = $this->buildSupplierGroups($order);

        $pdf = Pdf::loadView('order-barang.nota-supplier-pdf', [
            'order' => $order,
            'profil' => $profil,
            'logoDataUri' => $logoDataUri,
            'supplierGroups' => $supplierGroups,
        ])->setPaper('a4', 'portrait');

        $safeNomorOrder = str_replace(['/', '\\'], '-', (string) $order->nomor_order);

        return $pdf->stream('nota-order-supplier-'.$safeNomorOrder.'.pdf');
    }

    public function cetakSuratPermohonanPembayaran(OrderBarang $order)
    {
        $this->ensureAccessible($order);
        $order->load(['items.supplier', 'profilMbg']);
        $this->ensureNomorNotaAssigned($order);
        $order->load(['items.supplier', 'profilMbg']);

        $profil = ProfilMbg::query()->find(ProfilMbgTenant::id());
        $logoDataUri = $this->profilLogoDataUriForPdf($profil);

        $items = $order->items->values();
        $grandTotal = $items->sum(
            fn ($item): float => (float) $item->jumlah_barang * (float) $item->harga_barang
        );

        $rekeningSupplier = $this->buildSupplierGroups($order)->map(function (array $group) use ($order): array {
            $supplier = $group['items']->first()?->supplier;
            $subtotal = $group['items']->sum(
                fn ($item): float => (float) $item->jumlah_barang * (float) $item->harga_barang
            );

            return [
                'supplier_nama' => $group['supplier_nama'],
                'nomor_nota' => (string) ($group['nomor_nota'] ?: $order->nomor_order),
                'nama_bank' => trim((string) ($supplier?->nama_bank ?? '')),
                'nomor_rekening' => trim((string) ($supplier?->nomor_rekening ?? '')),
                'atas_nama_rekening' => trim((string) ($supplier?->atas_nama_rekening ?? '')),
                'subtotal' => $subtotal,
            ];
        })->values();

        $nomorSpm = $this->formatNomorSpm(
            (string) $order->nomor_order,
            $profil,
            $order->tanggal_order
        );

        $pdf = Pdf::loadView('order-barang.surat-permohonan-pembayaran-pdf', [
            'order' => $order,
            'profil' => $profil,
            'logoDataUri' => $logoDataUri,
            'items' => $items,
            'grandTotal' => $grandTotal,
            'rekeningSupplier' => $rekeningSupplier,
            'nomorSpm' => $nomorSpm,
        ])->setPaper('a4', 'portrait');

        $safeNomorOrder = str_replace(['/', '\\'], '-', (string) $order->nomor_order);

        return $pdf->stream('surat-permohonan-pembayaran-'.$safeNomorOrder.'.pdf');
    }

    /**
     * @return Collection<int, array{supplier_nama: string, nomor_nota: string, items: Collection}>
     */
    private function buildSupplierGroups(OrderBarang $order): Collection
    {
        return $order->items
            ->groupBy(fn ($item): string => $this->supplierGroupKey(
                $item->supplier_nama ?? $item->supplier?->nama_supplier
            ))
            ->map(function (Collection $items) use ($order): array {
                $first = $items->first();
                $nama = trim((string) ($first->supplier_nama ?? $first->supplier?->nama_supplier ?? ''));

                return [
                    'supplier_nama' => $nama !== '' ? $nama : 'Tanpa Supplier',
                    'nomor_nota' => (string) ($first->nomor_nota ?: $order->nomor_order),
                    'items' => $items->values(),
                ];
            })
            ->sortBy(fn (array $group): string => (string) $group['nomor_nota'])
            ->values();
    }

    private function formatNomorSpm(string $nomorNota, ?ProfilMbg $profil, mixed $tanggal): string
    {
        $seq = '001';
        if (preg_match('/^(\d{3})\//', $nomorNota, $m)) {
            $seq = $m[1];
        }

        $kode = trim((string) ($profil?->kode_dapur ?? ''));
        if ($kode === '') {
            $kode = 'SPPG';
        }

        $month = (int) (optional($tanggal)->month ?? now()->month);
        $year = (string) (optional($tanggal)->format('Y') ?? now()->format('Y'));
        $roman = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ][$month] ?? 'I';

        return $seq.'/'.$kode.'/SPM/'.$roman.'/'.$year;
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

        return $this->formatNomorSequence($this->lastSequenceNumber($suffix) + 1, $suffix);
    }

    /**
     * @return list<string>
     */
    private function allocateSequentialNomor(int $count): array
    {
        if ($count < 1) {
            return [];
        }

        $suffix = '/SPPG/PL/'.now()->format('m/Y');
        $next = $this->lastSequenceNumber($suffix) + 1;
        $numbers = [];

        for ($i = 0; $i < $count; $i++) {
            $numbers[] = $this->formatNomorSequence($next + $i, $suffix);
        }

        return $numbers;
    }

    private function lastSequenceNumber(string $suffix): int
    {
        $periodeId = PeriodeTenant::id();
        $profilId = ProfilMbgTenant::id();

        $lastOrder = OrderBarang::query()
            ->where('periode_id', $periodeId)
            ->where('profil_mbg_id', $profilId)
            ->where('nomor_order', 'like', '%'.$suffix)
            ->lockForUpdate()
            ->orderByDesc('nomor_order')
            ->value('nomor_order');

        $lastNota = OrderBarangItem::query()
            ->whereNotNull('nomor_nota')
            ->where('nomor_nota', 'like', '%'.$suffix)
            ->whereHas('orderBarang', function ($q) use ($periodeId, $profilId): void {
                $q->where('periode_id', $periodeId)->where('profil_mbg_id', $profilId);
            })
            ->lockForUpdate()
            ->orderByDesc('nomor_nota')
            ->value('nomor_nota');

        $max = 0;
        foreach ([$lastOrder, $lastNota] as $value) {
            if ($value && preg_match('/^(\d{3})\/SPPG\/PL\/\d{2}\/\d{4}$/', (string) $value, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max;
    }

    private function formatNomorSequence(int $number, string $suffix): string
    {
        return str_pad((string) $number, 3, '0', STR_PAD_LEFT).$suffix;
    }

    private function supplierGroupKey(?string $supplierNama): string
    {
        $nama = trim((string) $supplierNama);

        return $nama !== '' ? 'nama:'.mb_strtolower($nama) : 'tanpa-supplier';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, string>  $notaByKey
     */
    private function syncOrderItems(OrderBarang $order, array $rows, array $notaByKey = []): void
    {
        $groupedRows = [];
        foreach ($rows as $row) {
            $key = $this->supplierGroupKey($row['supplier_nama'] ?? null);
            $groupedRows[$key][] = $row;
        }

        ksort($groupedRows);

        $keysNeedingNota = [];
        foreach (array_keys($groupedRows) as $key) {
            if (empty($notaByKey[$key])) {
                $keysNeedingNota[] = $key;
            }
        }

        if ($keysNeedingNota !== []) {
            $newNumbers = $this->allocateSequentialNomor(count($keysNeedingNota));
            foreach ($keysNeedingNota as $index => $key) {
                $notaByKey[$key] = $newNumbers[$index];
            }
        }

        foreach ($groupedRows as $key => $groupRows) {
            $nomorNota = $notaByKey[$key];

            foreach ($groupRows as $row) {
                $barang = $this->resolveBarang((string) $row['nama_barang'], (float) $row['harga_barang']);
                $supplier = $this->resolveSupplier($row['supplier_nama'] ?? null);

                $order->items()->create([
                    'barang_id' => (int) $barang->getKey(),
                    'supplier_id' => $supplier?->getKey(),
                    'nama_barang' => $barang->nama_barang,
                    'supplier_nama' => $supplier?->nama_supplier ?? null,
                    'nomor_nota' => $nomorNota,
                    'harga_barang' => (float) $row['harga_barang'],
                    'jumlah_barang' => (float) $row['jumlah_barang'],
                    'satuan_barang' => (string) $row['satuan_barang'],
                    'jumlah_hari_pemakaian' => (int) ($row['jumlah_hari_pemakaian'] ?? 0),
                ]);
            }
        }
    }

    private function ensureNomorNotaAssigned(OrderBarang $order): void
    {
        if ($order->items->every(fn ($item): bool => filled($item->nomor_nota))) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $order->items()->lockForUpdate()->get();
            $order->load('items.supplier');

            $groups = $order->items->groupBy(fn ($item): string => $this->supplierGroupKey(
                $item->supplier_nama ?? $item->supplier?->nama_supplier
            ));

            $notaByKey = [];
            $keysNeedingNota = [];

            foreach ($groups as $key => $items) {
                $existing = $items->first(fn ($item): bool => filled($item->nomor_nota))?->nomor_nota;
                if ($existing) {
                    $notaByKey[$key] = (string) $existing;
                } else {
                    $keysNeedingNota[] = $key;
                }
            }

            if ($keysNeedingNota !== []) {
                $newNumbers = $this->allocateSequentialNomor(count($keysNeedingNota));
                foreach ($keysNeedingNota as $index => $key) {
                    $notaByKey[$key] = $newNumbers[$index];
                }
            }

            foreach ($groups as $key => $items) {
                OrderBarangItem::query()
                    ->whereIn('id', $items->pluck('id'))
                    ->update(['nomor_nota' => $notaByKey[$key]]);
            }
        });

        $order->unsetRelation('items');
    }

    private function validatePayload(Request $request, ?OrderBarang $order = null): array
    {
        $rules = [
            'tanggal_order' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_barang' => ['required', 'string', 'max:255'],
            'items.*.harga_barang' => ['required', 'numeric', 'min:0'],
            'items.*.jumlah_barang' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan_barang' => ['required', 'string', 'max:32'],
            'items.*.supplier_nama' => ['nullable', 'string', 'max:255'],
            'items.*.jumlah_hari_pemakaian' => ['required', 'integer', 'min:0', 'max:3650'],
        ];

        $attributes = [
            'tanggal_order' => 'tanggal order',
            'nomor_order' => 'nomor order',
            'items.*.nama_barang' => 'nama barang',
            'items.*.harga_barang' => 'harga barang',
            'items.*.jumlah_barang' => 'jumlah barang',
            'items.*.satuan_barang' => 'satuan barang',
            'items.*.supplier_nama' => 'nama supplier',
            'items.*.jumlah_hari_pemakaian' => 'pemakaian (hari)',
        ];

        if ($order !== null) {
            $rules['nomor_order'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('order_barang', 'nomor_order')
                    ->where(fn ($q) => $q
                        ->where('profil_mbg_id', ProfilMbgTenant::id())
                        ->where('periode_id', PeriodeTenant::id()))
                    ->ignore($order->getKey()),
            ];
        }

        return $request->validate($rules, [
            'nomor_order.unique' => 'Nomor order sudah dipakai pada periode ini.',
        ], $attributes);
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
