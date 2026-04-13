<?php

namespace App\Http\Controllers;

use App\Models\Barang;
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
        $barangs = Barang::query()->orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'harga_satuan', 'satuan']);
        $suppliers = Supplier::query()->orderBy('nama_supplier')->get(['id', 'nama_supplier']);

        return view('order-barang.create', [
            'barangs' => $barangs,
            'suppliers' => $suppliers,
            'previewNomorOrder' => $this->previewNextNomorOrder(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_order' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.barang_id' => ['required', 'integer', 'exists:barang,id'],
            'items.*.harga_barang' => ['required', 'numeric', 'min:0'],
            'items.*.jumlah_barang' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan_barang' => ['required', 'string', 'max:32'],
            'items.*.supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'items.*.jumlah_hari_pemakaian' => ['required', 'integer', 'min:0', 'max:3650'],
        ], [], [
            'tanggal_order' => 'tanggal order',
            'items.*.barang_id' => 'barang',
            'items.*.harga_barang' => 'harga barang',
            'items.*.jumlah_barang' => 'jumlah barang',
            'items.*.satuan_barang' => 'satuan barang',
            'items.*.supplier_id' => 'supplier',
            'items.*.jumlah_hari_pemakaian' => 'jumlah hari pemakaian',
        ]);

        DB::transaction(function () use ($request, $data): void {
            $order = OrderBarang::query()->create([
                'nomor_order' => $this->generateNomorOrder(),
                'profil_mbg_id' => ProfilMbgTenant::id(),
                'periode_id' => PeriodeTenant::id(),
                'tanggal_order' => $data['tanggal_order'],
                'created_by' => (int) $request->user()->getKey(),
            ]);

            $barangMap = Barang::query()
                ->whereIn('id', collect($data['items'])->pluck('barang_id')->all())
                ->get(['id', 'nama_barang'])
                ->keyBy('id');

            foreach ($data['items'] as $row) {
                $barang = $barangMap->get((int) $row['barang_id']);
                if (! $barang) {
                    continue;
                }

                $order->items()->create([
                    'barang_id' => (int) $row['barang_id'],
                    'supplier_id' => $row['supplier_id'] ? (int) $row['supplier_id'] : null,
                    'nama_barang' => $barang->nama_barang,
                    'harga_barang' => (float) $row['harga_barang'],
                    'jumlah_barang' => (float) $row['jumlah_barang'],
                    'satuan_barang' => (string) $row['satuan_barang'],
                    'jumlah_hari_pemakaian' => (int) $row['jumlah_hari_pemakaian'],
                ]);
            }
        });

        return redirect()
            ->route('stok.order.index')
            ->with('success', 'Order barang berhasil disimpan.');
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

        return $pdf->stream('nota-order-'.$order->nomor_order.'.pdf');
    }

    private function ensureAccessible(OrderBarang $order): void
    {
        if ((int) $order->profil_mbg_id !== ProfilMbgTenant::id() || (int) $order->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }

    private function previewNextNomorOrder(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        $last = OrderBarang::query()
            ->where('nomor_order', 'like', $prefix.'%')
            ->orderByDesc('nomor_order')
            ->value('nomor_order');
        $next = 1;
        if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function generateNomorOrder(): string
    {
        return DB::transaction(function (): string {
            $prefix = 'ORD-'.now()->format('Ymd').'-';
            $last = OrderBarang::query()
                ->where('nomor_order', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('nomor_order')
                ->value('nomor_order');

            $next = 1;
            if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
                $next = (int) $m[1] + 1;
            }

            return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
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
