<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilMbgRequest;
use App\Models\ProfilMbg;
use App\Support\ProfilMbgTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfilMbgController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('master.profil-mbg.edit');
    }

    public function edit(): View
    {
        $profil = ProfilMbg::query()->firstOrFail();

        return view('profil-mbg.edit', compact('profil'));
    }

    public function update(UpdateProfilMbgRequest $request): RedirectResponse
    {
        $profil_mbg = ProfilMbg::query()->firstOrFail();
        $data = Arr::except($request->validated(), ['logo']);

        if ($request->hasFile('logo')) {
            if ($profil_mbg->logo) {
                Storage::disk('public')->delete('logo-mbg/'.$profil_mbg->logo);
            }
            $data['logo'] = basename($request->file('logo')->store('logo-mbg', 'public'));
        }

        $profil_mbg->update($data);
        ProfilMbgTenant::forgetCached();

        return redirect()
            ->route('master.profil-mbg.edit')
            ->with('success', 'Profil cabang MBG berhasil diperbarui.');
    }
}
