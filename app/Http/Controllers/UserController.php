<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ProfilMbg;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = User::query()
            ->select('users.*')
            ->with(['profilMbg', 'roles']);

        if ($request->filled('status_filter') && in_array($request->string('status_filter')->toString(), ['aktif', 'nonaktif'], true)) {
            $query->where('users.status', $request->string('status_filter'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('role_badges', fn (User $user) => view('users.partials.role-badges', ['user' => $user])->render())
            ->addColumn('profil_label', fn (User $user) => e($user->profilMbg?->nama_dapur ?? '—'))
            ->addColumn('status_badge', fn (User $user) => view('users.partials.status-badge', ['user' => $user])->render())
            ->addColumn('aksi', fn (User $user) => view('users.partials.actions', ['user' => $user])->render())
            ->rawColumns(['role_badges', 'status_badge', 'aksi'])
            ->toJson();
    }

    public function create(): View
    {
        $profils = ProfilMbg::query()->where('status', 'aktif')->orderBy('nama_dapur')->get();

        return view('users.create', compact('profils'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'profil_mbg_id', 'status']);
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
        $data = $request->safe()->only(['name', 'email', 'profil_mbg_id', 'status']);

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

    public function destroy(User $user): RedirectResponse
    {
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
