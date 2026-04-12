<?php

namespace App\Http\Controllers;

use App\Models\KategoriLimbah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriLimbahController extends Controller
{
    public function index(Request $request): View
    {
        $query = KategoriLimbah::query()->orderBy('nama_kategori');

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', '%'.$search.'%')
                    ->orWhere('deskripsi', 'like', '%'.$search.'%');
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('kategori-limbah.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-limbah.create', ['kategori' => new KategoriLimbah]);
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

        KategoriLimbah::query()->create($data);

        return redirect()
            ->route('master.kategori-limbah.index')
            ->with('success', 'Kategori limbah berhasil ditambahkan.');
    }

    public function edit(KategoriLimbah $kategori_limbah): View
    {
        $this->authorizeKategoriWrite();

        return view('kategori-limbah.edit', ['kategori' => $kategori_limbah]);
    }

    public function update(Request $request, KategoriLimbah $kategori_limbah): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_kategori' => 'nama kategori',
            'deskripsi' => 'deskripsi',
        ]);

        $kategori_limbah->update($data);

        return redirect()
            ->route('master.kategori-limbah.index')
            ->with('success', 'Kategori limbah berhasil diperbarui.');
    }

    public function destroy(KategoriLimbah $kategori_limbah): RedirectResponse
    {
        $this->authorizeKategoriWrite();

        $kategori_limbah->delete();

        return redirect()
            ->route('master.kategori-limbah.index')
            ->with('success', 'Kategori limbah berhasil dihapus (soft delete).');
    }

    private function authorizeKategoriWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah kategori.');
        }
    }
}
