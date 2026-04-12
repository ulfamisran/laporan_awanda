<?php

namespace App\Http\Controllers;

use App\Models\KategoriDanaKeluar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriDanaKeluarController extends Controller
{
    public function index(Request $request): View
    {
        $query = KategoriDanaKeluar::query()->orderBy('nama_kategori');

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', '%'.$search.'%')
                    ->orWhere('deskripsi', 'like', '%'.$search.'%');
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('kategori-dana-keluar.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-dana-keluar.create', ['kategori' => new KategoriDanaKeluar]);
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

        KategoriDanaKeluar::query()->create($data);

        return redirect()
            ->route('master.kategori-dana-keluar.index')
            ->with('success', 'Kategori dana keluar berhasil ditambahkan.');
    }

    public function edit(KategoriDanaKeluar $kategori_dana_keluar): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-dana-keluar.edit', ['kategori' => $kategori_dana_keluar]);
    }

    public function update(Request $request, KategoriDanaKeluar $kategori_dana_keluar): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_kategori' => 'nama kategori',
            'deskripsi' => 'deskripsi',
        ]);

        $kategori_dana_keluar->update($data);

        return redirect()
            ->route('master.kategori-dana-keluar.index')
            ->with('success', 'Kategori dana keluar berhasil diperbarui.');
    }

    public function destroy(KategoriDanaKeluar $kategori_dana_keluar): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $kategori_dana_keluar->delete();

        return redirect()
            ->route('master.kategori-dana-keluar.index')
            ->with('success', 'Kategori dana keluar berhasil dihapus (soft delete).');
    }

    private function authorizeKategoriWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah kategori.');
        }
    }
}
