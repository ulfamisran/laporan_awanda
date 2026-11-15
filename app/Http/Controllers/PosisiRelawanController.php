<?php

namespace App\Http\Controllers;

use App\Models\PosisiRelawan;
use App\Models\Relawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosisiRelawanController extends Controller
{
    public function index(Request $request): View
    {
        $query = PosisiRelawan::query()->ordered();
        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_posisi', 'like', '%'.$q.'%')
                    ->orWhere('deskripsi', 'like', '%'.$q.'%');
            });
        }

        $items = $query->paginate(50)->withQueryString();

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

        $data['urutan'] = ((int) PosisiRelawan::query()->max('urutan')) + 10;

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
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ], [], [
            'nama_posisi' => 'nama posisi',
            'deskripsi' => 'deskripsi',
            'urutan' => 'urutan',
        ]);

        if (! array_key_exists('urutan', $data) || $data['urutan'] === null) {
            unset($data['urutan']);
        }

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

    public function move(Request $request, PosisiRelawan $posisi_relawan): RedirectResponse
    {
        $this->authorizePosisiWrite();

        $data = $request->validate([
            'arah' => ['required', 'in:up,down'],
        ]);

        DB::transaction(function () use ($posisi_relawan, $data) {
            $rows = PosisiRelawan::query()->ordered()->lockForUpdate()->get()->values();
            $index = $rows->search(fn (PosisiRelawan $row) => (int) $row->id === (int) $posisi_relawan->id);
            if ($index === false) {
                return;
            }

            $swapWith = $data['arah'] === 'up' ? $index - 1 : $index + 1;
            if ($swapWith < 0 || $swapWith >= $rows->count()) {
                return;
            }

            $a = $rows[$index];
            $b = $rows[$swapWith];
            $tmp = $a->urutan;
            $a->urutan = $b->urutan;
            $b->urutan = $tmp;

            if ((int) $a->urutan === (int) $b->urutan) {
                $a->urutan = ($swapWith < $index)
                    ? max(0, (int) $b->urutan - 1)
                    : (int) $b->urutan + 1;
            }

            $a->save();
            $b->save();
        });

        return redirect()
            ->route('master.posisi-relawan.index', $request->only('q', 'page'))
            ->with('success', 'Urutan posisi diperbarui. Data relawan akan mengikuti urutan ini.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorizePosisiWrite();

        $data = $request->validate([
            'urutan' => ['required', 'array'],
            'urutan.*' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['urutan'] as $id => $urutan) {
                PosisiRelawan::query()
                    ->whereKey((int) $id)
                    ->update(['urutan' => (int) $urutan]);
            }
        });

        return redirect()
            ->route('master.posisi-relawan.index', $request->only('q', 'page'))
            ->with('success', 'Urutan posisi disimpan. Data relawan diurutkan sesuai pengaturan ini.');
    }

    private function authorizePosisiWrite(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah posisi relawan.');
        }
    }
}
