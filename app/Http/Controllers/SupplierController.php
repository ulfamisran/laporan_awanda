<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::query()->orderBy('nama_supplier');
        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_supplier', 'like', '%'.$q.'%')
                    ->orWhere('no_hp', 'like', '%'.$q.'%')
                    ->orWhere('alamat', 'like', '%'.$q.'%');
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('supplier.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeSupplierWrite();

        return view('supplier.create', ['supplier' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSupplierWrite();

        $data = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:32'],
            'alamat' => ['required', 'string', 'max:5000'],
        ], [], [
            'nama_supplier' => 'nama supplier',
            'no_hp' => 'nomor HP',
            'alamat' => 'alamat',
        ]);

        Supplier::query()->create($data);

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorizeSupplierWrite();

        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorizeSupplierWrite();

        $data = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:32'],
            'alamat' => ['required', 'string', 'max:5000'],
        ], [], [
            'nama_supplier' => 'nama supplier',
            'no_hp' => 'nomor HP',
            'alamat' => 'alamat',
        ]);

        $supplier->update($data);

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorizeSupplierWrite();

        $supplier->delete();

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    private function authorizeSupplierWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah master supplier.');
        }
    }
}
