<?php

namespace App\Http\Controllers;

use App\Models\PermintaanInsentif;
use App\Models\ProfilMbg;
use App\Support\PeriodeTenant;
use App\Support\PdfLogoProfil;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermintaanInsentifController extends Controller
{
    public function index(): View
    {
        $rows = PermintaanInsentif::query()
            ->with(['creator', 'profilMbg'])
            ->where('profil_mbg_id', ProfilMbgTenant::id())
            ->where('periode_id', PeriodeTenant::id())
            ->orderByDesc('id')
            ->paginate(15);

        return view('permintaan-insentif.index', compact('rows'));
    }

    public function create(): View
    {
        $profil = ProfilMbg::query()->findOrFail(ProfilMbgTenant::id());
        $item = $this->makeDraftFromProfil($profil);

        return view('permintaan-insentif.form', [
            'item' => $item,
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $profilId = ProfilMbgTenant::id();

        $item = PermintaanInsentif::query()->create([
            ...$data,
            'profil_mbg_id' => $profilId,
            'periode_id' => PeriodeTenant::id(),
            'created_by' => $request->user()?->getKey(),
        ]);

        return redirect()
            ->route('permintaan-insentif.show', $item)
            ->with('success', 'Permintaan insentif berhasil disimpan.');
    }

    public function show(PermintaanInsentif $permintaanInsentif): View
    {
        $this->ensureScope($permintaanInsentif);
        $permintaanInsentif->load(['profilMbg', 'creator']);

        $profil = $permintaanInsentif->profilMbg;

        return view('permintaan-insentif.show', [
            'item' => $permintaanInsentif,
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
        ]);
    }

    public function edit(PermintaanInsentif $permintaanInsentif): View
    {
        $this->ensureScope($permintaanInsentif);
        $profil = ProfilMbg::query()->findOrFail((int) $permintaanInsentif->profil_mbg_id);

        return view('permintaan-insentif.form', [
            'item' => $permintaanInsentif,
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, PermintaanInsentif $permintaanInsentif): RedirectResponse
    {
        $this->ensureScope($permintaanInsentif);
        $data = $this->validated($request);
        $permintaanInsentif->update($data);

        return redirect()
            ->route('permintaan-insentif.show', $permintaanInsentif)
            ->with('success', 'Permintaan insentif berhasil diperbarui.');
    }

    public function destroy(PermintaanInsentif $permintaanInsentif): RedirectResponse
    {
        $this->ensureScope($permintaanInsentif);
        $permintaanInsentif->delete();

        return redirect()
            ->route('permintaan-insentif.index')
            ->with('success', 'Permintaan insentif berhasil dihapus.');
    }

    public function cetakPdf(PermintaanInsentif $permintaanInsentif)
    {
        $this->ensureScope($permintaanInsentif);
        $profil = ProfilMbg::query()->findOrFail((int) $permintaanInsentif->profil_mbg_id);
        $logoDataUri = PdfLogoProfil::dataUri($profil);

        $pdf = Pdf::loadView('permintaan-insentif.pdf', [
            'item' => $permintaanInsentif,
            'profil' => $profil,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait');

        $slug = str($permintaanInsentif->judul_surat)->slug()->limit(40, '')->toString();

        return $pdf->stream('permintaan-insentif-'.$permintaanInsentif->getKey().'-'.$slug.'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'judul_surat' => ['required', 'string', 'max:255'],
            'periode_label' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'max:255'],
            'nama_sppg' => ['required', 'string', 'max:255'],
            'nama_peminta' => ['required', 'string', 'max:255'],
            'jabatan_peminta' => ['required', 'string', 'max:255'],
            'tanggal_perihal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'perihal_teks' => ['nullable', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'tempat_ttd' => ['nullable', 'string', 'max:255'],
            'tanggal_ttd' => ['nullable', 'date'],
            'ttd_mengetahui_jabatan' => ['nullable', 'string', 'max:255'],
            'ttd_mengetahui_nama' => ['nullable', 'string', 'max:255'],
            'ttd_menyetujui_jabatan' => ['nullable', 'string', 'max:255'],
            'ttd_menyetujui_nama' => ['nullable', 'string', 'max:255'],
        ]);

        $data['jumlah'] = round((float) $data['jumlah'], 2);

        return $data;
    }

    private function makeDraftFromProfil(ProfilMbg $profil): PermintaanInsentif
    {
        $today = now();
        $namaSppg = trim((string) ($profil->daerah_sppg ?: $profil->nama_dapur)) ?: 'SPPG';
        $tempat = trim((string) ($profil->tempat_pelaporan ?: $profil->kota)) ?: '—';

        $program = 'Makan Bergizi Gratis';
        $tglLabel = $today->translatedFormat('d F Y');
        $jumlahLabel = 'Rp. '.number_format(0, 0, ',', '.');

        return new PermintaanInsentif([
            'judul_surat' => 'SURAT PERINTAH PEMBAYARAN INSENTIF PIC SEKOLAH',
            'periode_label' => $today->copy()->startOfMonth()->translatedFormat('d').'-'.$today->translatedFormat('d F Y'),
            'program' => $program,
            'nama_sppg' => $namaSppg,
            'nama_peminta' => trim((string) ($profil->nama_akuntansi ?: '')) ?: '',
            'jabatan_peminta' => 'PLO Keuangan',
            'tanggal_perihal' => $today->toDateString(),
            'jumlah' => 0,
            'perihal_teks' => 'Dengan ini mengajukan Pembayaran Insentif PIC Sekolah untuk Operasional SPPG pada Program '.$program.' '.$tglLabel.' sebesar '.$jumlahLabel.'.',
            'catatan' => 'Dana ini akan digunakan untuk mendukung kelancaran Program Makan Bergizi Gratis dan dipertanggungjawabkan sesuai prosedur yang berlaku di BGN.',
            'tempat_ttd' => $tempat,
            'tanggal_ttd' => $today->toDateString(),
            'ttd_mengetahui_jabatan' => 'PIC '.trim((string) ($profil->nama_yayasan ?: 'Yayasan')),
            'ttd_mengetahui_nama' => trim((string) ($profil->ketua_yayasan ?: '')),
            'ttd_menyetujui_jabatan' => 'Kepala SPPG',
            'ttd_menyetujui_nama' => trim((string) ($profil->penanggung_jawab ?: '')),
        ]);
    }

    private function ensureScope(PermintaanInsentif $item): void
    {
        abort_unless((int) $item->profil_mbg_id === (int) ProfilMbgTenant::id(), 404);
        abort_unless((int) $item->periode_id === (int) PeriodeTenant::id(), 404);
    }
}
