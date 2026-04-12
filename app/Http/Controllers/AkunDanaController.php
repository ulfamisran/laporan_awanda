<?php

namespace App\Http\Controllers;

use App\Models\AkunDana;
use App\Models\StokDanaAwalAkun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AkunDanaController extends Controller
{
    public function index(Request $request): View
    {
        $items = AkunDana::query()
            ->with('parent')
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        return view('akun-dana.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeWrite();

        return view('akun-dana.form', [
            'akun' => new AkunDana,
            'mode' => 'create',
            'parents' => AkunDana::query()->where('is_grup', true)->orderBy('urutan')->orderBy('kode')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeWrite();

        $data = $request->validate([
            'kode' => ['required', 'string', 'max:32', 'unique:akun_dana,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:akun_dana,id'],
            'urutan' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_grup' => ['required', Rule::in(['0', '1', 0, 1])],
        ]);
        $data['is_grup'] = in_array($data['is_grup'], [1, '1'], true);
        $data['parent_id'] = ! empty($data['parent_id']) ? (int) $data['parent_id'] : null;

        if (! empty($data['parent_id'])) {
            $p = AkunDana::query()->find((int) $data['parent_id']);
            if ($p && ! $p->is_grup) {
                return back()->withInput()->withErrors(['parent_id' => 'Induk harus berupa akun grup.']);
            }
        }

        AkunDana::query()->create($data);

        return redirect()
            ->route('master.akun-dana.index')
            ->with('success', 'Akun dana berhasil ditambahkan.');
    }

    public function edit(AkunDana $akun_dana): View
    {
        $this->authorizeWrite();

        $parents = AkunDana::query()
            ->where('is_grup', true)
            ->where('id', '!=', $akun_dana->id)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        return view('akun-dana.form', [
            'akun' => $akun_dana,
            'mode' => 'edit',
            'parents' => $parents,
        ]);
    }

    public function update(Request $request, AkunDana $akun_dana): RedirectResponse
    {
        $this->authorizeWrite();

        $data = $request->validate([
            'kode' => ['required', 'string', 'max:32', Rule::unique('akun_dana', 'kode')->ignore($akun_dana->id)],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:akun_dana,id', Rule::notIn([$akun_dana->id])],
            'urutan' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_grup' => ['required', Rule::in(['0', '1', 0, 1])],
        ]);
        $data['is_grup'] = in_array($data['is_grup'], [1, '1'], true);
        $data['parent_id'] = ! empty($data['parent_id']) ? (int) $data['parent_id'] : null;

        if (! empty($data['parent_id'])) {
            $pid = (int) $data['parent_id'];
            if ($pid === (int) $akun_dana->id) {
                return back()->withInput()->withErrors(['parent_id' => 'Akun tidak boleh menjadi induk dirinya sendiri.']);
            }
            $p = AkunDana::query()->find($pid);
            if ($p && ! $p->is_grup) {
                return back()->withInput()->withErrors(['parent_id' => 'Induk harus berupa akun grup.']);
            }
            if ($this->newParentIsUnderNode($akun_dana, $pid)) {
                return back()->withInput()->withErrors(['parent_id' => 'Induk tidak valid (siklus hierarki).']);
            }
        }

        $akun_dana->update($data);

        return redirect()
            ->route('master.akun-dana.index')
            ->with('success', 'Akun dana diperbarui.');
    }

    public function destroy(AkunDana $akun_dana): RedirectResponse
    {
        $this->authorizeWrite();

        if ($akun_dana->children()->exists()) {
            return redirect()
                ->route('master.akun-dana.index')
                ->with('warning', 'Hapus sub-akun terlebih dahulu.');
        }

        if (StokDanaAwalAkun::query()->where('akun_dana_id', $akun_dana->id)->exists()) {
            return redirect()
                ->route('master.akun-dana.index')
                ->with('warning', 'Akun sudah dipakai pada stok dana awal; tidak dapat dihapus.');
        }

        $akun_dana->delete();

        return redirect()
            ->route('master.akun-dana.index')
            ->with('success', 'Akun dana dihapus.');
    }

    /** True if $newParentId is the node itself or a descendant of $node (would create a cycle). */
    private function newParentIsUnderNode(AkunDana $node, int $newParentId): bool
    {
        $walk = AkunDana::query()->find($newParentId);
        while ($walk) {
            if ((int) $walk->id === (int) $node->id) {
                return true;
            }
            $walk = $walk->parent_id ? AkunDana::query()->find($walk->parent_id) : null;
        }

        return false;
    }

    private function authorizeWrite(): void
    {
        $u = auth()->user();
        if (! $u || ! $u->hasAnyRole(['super_admin', 'admin_pusat'])) {
            abort(403, 'Hanya Super Admin atau Admin Pusat yang dapat mengubah master akun dana.');
        }
    }
}
