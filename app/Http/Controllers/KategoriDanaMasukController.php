<?php

namespace App\Http\Controllers;

use App\Models\KategoriDanaMasuk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriDanaMasukController extends Controller
{
    public function index(Request $request): View
    {
        $query = KategoriDanaMasuk::query()->orderBy('nama_kategori');

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', '%'.$search.'%')
                    ->orWhere('deskripsi', 'like', '%'.$search.'%');
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('kategori-dana-masuk.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-dana-masuk.create', ['kategori' => new KategoriDanaMasuk]);
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

        KategoriDanaMasuk::query()->create($data);

        return redirect()
            ->route('master.kategori-dana-masuk.index')
            ->with('success', 'Kategori dana masuk berhasil ditambahkan.');
    }

    public function edit(KategoriDanaMasuk $kategori_dana_masuk): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-dana-masuk.edit', ['kategori' => $kategori_dana_masuk]);
    }

    public function update(Request $request, KategoriDanaMasuk $kategori_dana_masuk): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_kategori' => 'nama kategori',
            'deskripsi' => 'deskripsi',
        ]);

        $kategori_dana_masuk->update($data);

        return redirect()
            ->route('master.kategori-dana-masuk.index')
            ->with('success', 'Kategori dana masuk berhasil diperbarui.');
    }

    public function destroy(KategoriDanaMasuk $kategori_dana_masuk): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $kategori_dana_masuk->delete();

        return redirect()
            ->route('master.kategori-dana-masuk.index')
            ->with('success', 'Kategori dana masuk berhasil dihapus (soft delete).');
    }

    private function authorizeKategoriWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah kategori.');
        }
    }
}
