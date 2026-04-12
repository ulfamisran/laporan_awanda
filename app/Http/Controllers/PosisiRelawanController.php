<?php

namespace App\Http\Controllers;

use App\Models\PosisiRelawan;
use App\Models\Relawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosisiRelawanController extends Controller
{
    public function index(Request $request): View
    {
        $query = PosisiRelawan::query()->orderBy('nama_posisi');
        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_posisi', 'like', '%'.$q.'%')
                    ->orWhere('deskripsi', 'like', '%'.$q.'%');
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('posisi-relawan.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizePosisiWrite();

        return view('posisi-relawan.create', ['posisi' => new PosisiRelawan]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePosisiWrite();

        $data = $request->validate([
            'nama_posisi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_posisi' => 'nama posisi',
            'deskripsi' => 'deskripsi',
        ]);

        PosisiRelawan::query()->create($data);

        return redirect()
            ->route('master.posisi-relawan.index')
            ->with('success', 'Posisi relawan berhasil ditambahkan.');
    }

    public function edit(PosisiRelawan $posisi_relawan): View
    {
        $this->authorizePosisiWrite();

        return view('posisi-relawan.edit', ['posisi' => $posisi_relawan]);
    }

    public function update(Request $request, PosisiRelawan $posisi_relawan): RedirectResponse
    {
        $this->authorizePosisiWrite();

        $data = $request->validate([
            'nama_posisi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'nama_posisi' => 'nama posisi',
            'deskripsi' => 'deskripsi',
        ]);

        $posisi_relawan->update($data);

        return redirect()
            ->route('master.posisi-relawan.index')
            ->with('success', 'Posisi relawan berhasil diperbarui.');
    }

    public function destroy(PosisiRelawan $posisi_relawan): RedirectResponse
    {
        $this->authorizePosisiWrite();

        if (Relawan::withTrashed()->where('posisi_relawan_id', $posisi_relawan->getKey())->exists()) {
            return redirect()
                ->route('master.posisi-relawan.index')
                ->with('error', 'Posisi tidak dapat dihapus karena masih digunakan oleh data relawan.');
        }

        $posisi_relawan->delete();

        return redirect()
            ->route('master.posisi-relawan.index')
            ->with('success', 'Posisi relawan berhasil dihapus (soft delete).');
    }

    private function authorizePosisiWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah posisi relawan.');
        }
    }
}
