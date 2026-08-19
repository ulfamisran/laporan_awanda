<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ProfilMbg;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['profilMbg', 'roles'])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $keyword = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($keyword): void {
                $q->where('name', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword);
            });
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['aktif', 'nonaktif'], true)) {
            $query->where('status', $request->string('status'));
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $profils = ProfilMbg::query()->where('status', 'aktif')->orderBy('nama_dapur')->get();

        return view('users.create', compact('profils'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'status']);
        $data['profil_mbg_id'] = $request->validated('role') === 'admin'
            ? $request->validated('profil_mbg_id')
            : null;
        $data['password'] = Hash::make($request->validated('password'));

        if ($request->hasFile('foto')) {
            $data['foto'] = basename($request->file('foto')->store('foto-user', 'public'));
        }

        $user = User::query()->create($data);
        $user->syncRoles([$request->validated('role')]);

        return redirect()
            ->route('master.pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $profils = ProfilMbg::query()->where('status', 'aktif')->orderBy('nama_dapur')->get();

        return view('users.edit', compact('user', 'profils'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'status']);
        $data['profil_mbg_id'] = $request->validated('role') === 'admin'
            ? $request->validated('profil_mbg_id')
            : null;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete('foto-user/'.$user->foto);
            }
            $data['foto'] = basename($request->file('foto')->store('foto-user', 'public'));
        }

        $user->update($data);
        $user->syncRoles([$request->validated('role')]);

        return redirect()
            ->route('master.pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()?->getKey() === (int) $user->getKey()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->foto) {
            Storage::disk('public')->delete('foto-user/'.$user->foto);
        }

        $user->delete();

        return redirect()
            ->route('master.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus (soft delete).');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $user->password = Hash::make('password123');
        $user->save();

        return back()->with('success', 'Kata sandi pengguna telah direset ke password123.');
    }
}
