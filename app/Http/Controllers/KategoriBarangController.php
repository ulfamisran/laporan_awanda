<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriBarangController extends Controller
{
    public function index(Request $request): View
    {
        $query = KategoriBarang::query()->orderBy('nama_kategori');

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', '%'.$search.'%')
                    ->orWhere('deskripsi', 'like', '%'.$search.'%');
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('kategori-barang.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-barang.create', ['kategori' => new KategoriBarang]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_kategori' => 'nama kategori',
            'deskripsi' => 'deskripsi',
        ]);

        KategoriBarang::query()->create($data);

        return redirect()
            ->route('master.kategori-barang.index')
            ->with('success', 'Kategori barang berhasil ditambahkan.');
    }

    public function edit(KategoriBarang $kategori_barang): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-barang.edit', ['kategori' => $kategori_barang]);
    }

    public function update(Request $request, KategoriBarang $kategori_barang): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_kategori' => 'nama kategori',
            'deskripsi' => 'deskripsi',
        ]);

        $kategori_barang->update($data);

        return redirect()
            ->route('master.kategori-barang.index')
            ->with('success', 'Kategori barang berhasil diperbarui.');
    }

    public function destroy(KategoriBarang $kategori_barang): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $kategori_barang->delete();

        return redirect()
            ->route('master.kategori-barang.index')
            ->with('success', 'Kategori barang berhasil dihapus (soft delete).');
    }

    private function authorizeKategoriWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah kategori.');
        }
    }
}
