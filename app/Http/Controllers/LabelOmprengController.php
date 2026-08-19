<?php

namespace App\Http\Controllers;

use App\Models\LabelOmpreng;
use App\Models\LabelOmprengItem;
use App\Models\ProfilMbg;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LabelOmprengController extends Controller
{
    public function index(): View
    {
        $items = LabelOmpreng::query()
            ->with('creator')
            ->where('profil_mbg_id', ProfilMbgTenant::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('label-ompreng.index', compact('items'));
    }

    public function create(): View
    {
        $profil = ProfilMbg::query()->first();

        return view('label-ompreng.create', [
            'pageTitle' => 'Tambah Label Ompreng',
            'heading' => 'Tambah Label Ompreng',
            'submitLabel' => 'Simpan data label',
            'formAction' => route('label-ompreng.store'),
            'formMethod' => 'POST',
            'initialLabels' => null,
            'defaultLokasi' => $this->defaultLokasi($profil),
            'defaultPeringatan' => $this->defaultPeringatan(),
            'defaultHimbauan' => $this->defaultHimbauan(),
            'defaultTemplate' => 'premium',
            'logoDataUri' => $this->logoDataUriForPdf($profil),
        ]);
    }

    public function createMobile(): View
    {
        $profil = ProfilMbg::query()->first();

        return view('label-ompreng.mobile-create', [
            'pageTitle' => 'Input Label Ompreng Mobile',
            'heading' => 'Input Label Ompreng Mobile',
            'submitLabel' => 'Simpan data label',
            'formAction' => route('label-ompreng.store'),
            'formMethod' => 'POST',
            'initialLabels' => null,
            'defaultLokasi' => $this->defaultLokasi($profil),
            'defaultPeringatan' => $this->defaultPeringatan(),
            'defaultHimbauan' => $this->defaultHimbauan(),
            'defaultTemplate' => 'premium',
        ]);
    }

    public function edit(LabelOmpreng $labelOmpreng): View
    {
        $this->ensureAccessible($labelOmpreng);
        $profil = ProfilMbg::query()->first();
        $labelOmpreng->load('items');

        return view('label-ompreng.create', [
            'pageTitle' => 'Update Label Ompreng',
            'heading' => 'Update Label Ompreng',
            'submitLabel' => 'Update data label',
            'formAction' => route('label-ompreng.update', $labelOmpreng),
            'formMethod' => 'PUT',
            'initialLabels' => $labelOmpreng->items->map(fn (LabelOmprengItem $item): array => $this->itemForForm($item))->values()->all(),
            'defaultLokasi' => (string) $labelOmpreng->lokasi_sppg,
            'defaultPeringatan' => (string) $labelOmpreng->peringatan,
            'defaultHimbauan' => (string) $labelOmpreng->himbauan,
            'defaultTemplate' => (string) $labelOmpreng->template_default,
            'logoDataUri' => $this->logoDataUriForPdf($profil),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        $profilId = ProfilMbgTenant::id();
        $created = DB::transaction(function () use ($request, $data, $profilId): LabelOmpreng {
            $labelOmpreng = LabelOmpreng::query()->create([
                'profil_mbg_id' => $profilId,
                'created_by' => $request->user()?->getKey(),
                'template_default' => (string) $data['template'],
                'lokasi_sppg' => trim((string) $data['lokasi_sppg']),
                'peringatan' => trim((string) $data['peringatan']),
                'himbauan' => trim((string) $data['himbauan']),
                'jumlah_item' => count($data['labels']),
            ]);

            foreach (array_values($data['labels']) as $index => $row) {
                $labelOmpreng->items()->create($this->itemPayload($row, $index + 1));
            }

            return $labelOmpreng;
        });

        return redirect()
            ->route('label-ompreng.show', $created)
            ->with('success', 'Data label ompreng berhasil disimpan.');
    }

    public function update(Request $request, LabelOmpreng $labelOmpreng): RedirectResponse
    {
        $this->ensureAccessible($labelOmpreng);
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($labelOmpreng, $data): void {
            $labelOmpreng->update([
                'template_default' => (string) $data['template'],
                'lokasi_sppg' => trim((string) $data['lokasi_sppg']),
                'peringatan' => trim((string) $data['peringatan']),
                'himbauan' => trim((string) $data['himbauan']),
                'jumlah_item' => count($data['labels']),
            ]);

            $labelOmpreng->items()->delete();
            foreach (array_values($data['labels']) as $index => $row) {
                $labelOmpreng->items()->create($this->itemPayload($row, $index + 1));
            }
        });

        return redirect()
            ->route('label-ompreng.show', $labelOmpreng)
            ->with('success', 'Data label ompreng berhasil diperbarui.');
    }

    public function show(LabelOmpreng $labelOmpreng): View
    {
        $this->ensureAccessible($labelOmpreng);
        $labelOmpreng->load('items', 'creator');

        $profil = ProfilMbg::query()->first();

        return view('label-ompreng.show', [
            'labelOmpreng' => $labelOmpreng,
            'logoDataUri' => $this->logoDataUriForPdf($profil),
        ]);
    }

    public function preview(Request $request, LabelOmpreng $labelOmpreng): Response
    {
        $this->ensureAccessible($labelOmpreng);

        $data = $request->validate([
            'template' => ['nullable', 'string', 'in:premium'],
            'jumlah_cetak' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $pdf = $this->buildPdfFromStored(
            $labelOmpreng,
            (string) ($data['template'] ?? $labelOmpreng->template_default ?? 'premium'),
            (int) ($data['jumlah_cetak'] ?? max(1, $labelOmpreng->jumlah_item))
        );

        return $pdf->stream('label-ompreng-'.$labelOmpreng->id.'.pdf');
    }

    public function cetak(Request $request, LabelOmpreng $labelOmpreng): Response
    {
        $this->ensureAccessible($labelOmpreng);

        $data = $request->validate([
            'template' => ['nullable', 'string', 'in:premium'],
            'jumlah_cetak' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $pdf = $this->buildPdfFromStored(
            $labelOmpreng,
            (string) ($data['template'] ?? $labelOmpreng->template_default ?? 'premium'),
            (int) ($data['jumlah_cetak'] ?? max(1, $labelOmpreng->jumlah_item))
        );

        return $pdf->download('label-ompreng-'.$labelOmpreng->id.'.pdf');
    }

    private function buildPdfFromStored(LabelOmpreng $labelOmpreng, string $template, int $jumlahCetak)
    {
        $labelOmpreng->loadMissing('items');
        $sourceItems = $labelOmpreng->items->values();
        abort_if($sourceItems->isEmpty(), 422, 'Data label ompreng belum memiliki detail item.');

        $labels = collect(range(0, max(1, $jumlahCetak) - 1))
            ->map(function (int $index) use ($sourceItems): array {
                /** @var LabelOmprengItem $item */
                $item = $sourceItems[$index % $sourceItems->count()];

                return $this->mapItemForPdf($item);
            })
            ->values();

        $pages = $labels->chunk(3)->map(fn (Collection $chunk): array => $chunk->values()->all())->values();
        $profil = ProfilMbg::query()->first();

        $pdf = Pdf::loadView('label-ompreng.pdf', [
            'pages' => $pages,
            'template' => $template,
            'lokasiSppg' => (string) $labelOmpreng->lokasi_sppg,
            'peringatan' => (string) $labelOmpreng->peringatan,
            'himbauan' => (string) $labelOmpreng->himbauan,
            'logoDataUri' => $this->logoDataUriForPdf($profil),
        ])->setPaper('a4', 'portrait');

        return $pdf;
    }

    private function mapItemForPdf(LabelOmprengItem $item): array
    {
        return [
            'menu' => [
                (string) $item->menu_1,
                (string) $item->menu_2,
                (string) $item->menu_3,
                (string) $item->menu_4,
                (string) $item->menu_5,
            ],
            'gizi_groups' => $this->buildGiziGroups($item),
            'tanggal_produksi' => optional($item->tanggal_produksi)->format('d-m-y') ?: '-',
            'batas_konsumsi' => substr((string) $item->batas_konsumsi, 0, 5).' - '.substr((string) $item->batas_konsumsi_sampai, 0, 5),
        ];
    }

    private function buildGiziGroups(LabelOmprengItem $item): array
    {
        return [
            [
                'label' => 'Porsi Besar',
                'items' => [
                    ['label' => 'Energi', 'nilai' => $this->formatNutrisi($item->porsi_besar_energi_nilai ?? $item->energi_nilai, $item->energi_satuan)],
                    ['label' => 'Protein', 'nilai' => $this->formatNutrisi($item->porsi_besar_protein_nilai ?? $item->protein_nilai, $item->protein_satuan)],
                    ['label' => 'Lemak', 'nilai' => $this->formatNutrisi($item->porsi_besar_lemak_nilai ?? $item->lemak_nilai, $item->lemak_satuan)],
                    ['label' => 'Karbohidrat', 'nilai' => $this->formatNutrisi($item->porsi_besar_karbohidrat_nilai ?? $item->karbohidrat_nilai, $item->karbohidrat_satuan)],
                    ['label' => 'Serat', 'nilai' => $this->formatNutrisi($item->porsi_besar_serat_nilai ?? $item->serat_nilai, $item->serat_satuan)],
                ],
            ],
            [
                'label' => 'Porsi Kecil',
                'items' => [
                    ['label' => 'Energi', 'nilai' => $this->formatNutrisi($item->porsi_kecil_energi_nilai, $item->energi_satuan)],
                    ['label' => 'Protein', 'nilai' => $this->formatNutrisi($item->porsi_kecil_protein_nilai, $item->protein_satuan)],
                    ['label' => 'Lemak', 'nilai' => $this->formatNutrisi($item->porsi_kecil_lemak_nilai, $item->lemak_satuan)],
                    ['label' => 'Karbohidrat', 'nilai' => $this->formatNutrisi($item->porsi_kecil_karbohidrat_nilai, $item->karbohidrat_satuan)],
                    ['label' => 'Serat', 'nilai' => $this->formatNutrisi($item->porsi_kecil_serat_nilai, $item->serat_satuan)],
                ],
            ],
            [
                'label' => 'Balita',
                'items' => [
                    ['label' => 'Energi', 'nilai' => $this->formatNutrisi($item->balita_energi_nilai, $item->energi_satuan)],
                    ['label' => 'Protein', 'nilai' => $this->formatNutrisi($item->balita_protein_nilai, $item->protein_satuan)],
                    ['label' => 'Lemak', 'nilai' => $this->formatNutrisi($item->balita_lemak_nilai, $item->lemak_satuan)],
                    ['label' => 'Karbohidrat', 'nilai' => $this->formatNutrisi($item->balita_karbohidrat_nilai, $item->karbohidrat_satuan)],
                    ['label' => 'Serat', 'nilai' => $this->formatNutrisi($item->balita_serat_nilai, $item->serat_satuan)],
                ],
            ],
            [
                'label' => 'Bumil/Busui',
                'items' => [
                    ['label' => 'Energi', 'nilai' => $this->formatNutrisi($item->bumil_busui_energi_nilai, $item->energi_satuan)],
                    ['label' => 'Protein', 'nilai' => $this->formatNutrisi($item->bumil_busui_protein_nilai, $item->protein_satuan)],
                    ['label' => 'Lemak', 'nilai' => $this->formatNutrisi($item->bumil_busui_lemak_nilai, $item->lemak_satuan)],
                    ['label' => 'Karbohidrat', 'nilai' => $this->formatNutrisi($item->bumil_busui_karbohidrat_nilai, $item->karbohidrat_satuan)],
                    ['label' => 'Serat', 'nilai' => $this->formatNutrisi($item->bumil_busui_serat_nilai, $item->serat_satuan)],
                ],
            ],
        ];
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'lokasi_sppg' => ['required', 'string', 'max:255'],
            'template' => ['nullable', 'string', 'in:premium'],
            'peringatan' => ['required', 'string', 'max:1000'],
            'himbauan' => ['required', 'string', 'max:1000'],
            'labels' => ['required', 'array', 'min:1'],
            'labels.*.menu_1' => ['required', 'string', 'max:255'],
            'labels.*.menu_2' => ['required', 'string', 'max:255'],
            'labels.*.menu_3' => ['required', 'string', 'max:255'],
            'labels.*.menu_4' => ['required', 'string', 'max:255'],
            'labels.*.menu_5' => ['required', 'string', 'max:255'],
            'labels.*.energi_satuan' => ['nullable', 'string', 'max:20'],
            'labels.*.protein_satuan' => ['nullable', 'string', 'max:20'],
            'labels.*.lemak_satuan' => ['nullable', 'string', 'max:20'],
            'labels.*.karbohidrat_satuan' => ['nullable', 'string', 'max:20'],
            'labels.*.serat_satuan' => ['nullable', 'string', 'max:20'],
            'labels.*.porsi_besar_energi_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_besar_protein_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_besar_lemak_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_besar_karbohidrat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_besar_serat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_kecil_energi_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_kecil_protein_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_kecil_lemak_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_kecil_karbohidrat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.porsi_kecil_serat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.balita_energi_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.balita_protein_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.balita_lemak_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.balita_karbohidrat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.balita_serat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.bumil_busui_energi_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.bumil_busui_protein_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.bumil_busui_lemak_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.bumil_busui_karbohidrat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.bumil_busui_serat_nilai' => ['nullable', 'numeric', 'min:0'],
            'labels.*.tanggal_produksi' => ['required', 'date'],
            'labels.*.batas_konsumsi_dari' => ['required', 'date_format:H:i'],
            'labels.*.batas_konsumsi_sampai' => ['required', 'date_format:H:i'],
        ]);
    }

    private function itemPayload(array $row, int $urutan): array
    {
        return [
            'urutan' => $urutan,
            'menu_1' => trim((string) $row['menu_1']),
            'menu_2' => trim((string) $row['menu_2']),
            'menu_3' => trim((string) $row['menu_3']),
            'menu_4' => trim((string) $row['menu_4']),
            'menu_5' => trim((string) $row['menu_5']),
            'energi_satuan' => $this->normalizeUnit($row['energi_satuan'] ?? null),
            'protein_satuan' => $this->normalizeUnit($row['protein_satuan'] ?? null),
            'lemak_satuan' => $this->normalizeUnit($row['lemak_satuan'] ?? null),
            'karbohidrat_satuan' => $this->normalizeUnit($row['karbohidrat_satuan'] ?? null),
            'serat_satuan' => $this->normalizeUnit($row['serat_satuan'] ?? null),
            'porsi_besar_energi_nilai' => $row['porsi_besar_energi_nilai'] ?? null,
            'porsi_besar_protein_nilai' => $row['porsi_besar_protein_nilai'] ?? null,
            'porsi_besar_lemak_nilai' => $row['porsi_besar_lemak_nilai'] ?? null,
            'porsi_besar_karbohidrat_nilai' => $row['porsi_besar_karbohidrat_nilai'] ?? null,
            'porsi_besar_serat_nilai' => $row['porsi_besar_serat_nilai'] ?? null,
            'porsi_kecil_energi_nilai' => $row['porsi_kecil_energi_nilai'] ?? null,
            'porsi_kecil_protein_nilai' => $row['porsi_kecil_protein_nilai'] ?? null,
            'porsi_kecil_lemak_nilai' => $row['porsi_kecil_lemak_nilai'] ?? null,
            'porsi_kecil_karbohidrat_nilai' => $row['porsi_kecil_karbohidrat_nilai'] ?? null,
            'porsi_kecil_serat_nilai' => $row['porsi_kecil_serat_nilai'] ?? null,
            'balita_energi_nilai' => $row['balita_energi_nilai'] ?? null,
            'balita_protein_nilai' => $row['balita_protein_nilai'] ?? null,
            'balita_lemak_nilai' => $row['balita_lemak_nilai'] ?? null,
            'balita_karbohidrat_nilai' => $row['balita_karbohidrat_nilai'] ?? null,
            'balita_serat_nilai' => $row['balita_serat_nilai'] ?? null,
            'bumil_busui_energi_nilai' => $row['bumil_busui_energi_nilai'] ?? null,
            'bumil_busui_protein_nilai' => $row['bumil_busui_protein_nilai'] ?? null,
            'bumil_busui_lemak_nilai' => $row['bumil_busui_lemak_nilai'] ?? null,
            'bumil_busui_karbohidrat_nilai' => $row['bumil_busui_karbohidrat_nilai'] ?? null,
            'bumil_busui_serat_nilai' => $row['bumil_busui_serat_nilai'] ?? null,
            'tanggal_produksi' => $row['tanggal_produksi'],
            'batas_konsumsi' => $row['batas_konsumsi_dari'].':00',
            'batas_konsumsi_sampai' => $row['batas_konsumsi_sampai'].':00',
        ];
    }

    private function itemForForm(LabelOmprengItem $item): array
    {
        return [
            'menu_1' => (string) $item->menu_1,
            'menu_2' => (string) $item->menu_2,
            'menu_3' => (string) $item->menu_3,
            'menu_4' => (string) $item->menu_4,
            'menu_5' => (string) $item->menu_5,
            'energi_satuan' => (string) $item->energi_satuan,
            'protein_satuan' => (string) $item->protein_satuan,
            'lemak_satuan' => (string) $item->lemak_satuan,
            'karbohidrat_satuan' => (string) $item->karbohidrat_satuan,
            'serat_satuan' => (string) $item->serat_satuan,
            'porsi_besar_energi_nilai' => $item->porsi_besar_energi_nilai ?? $item->energi_nilai,
            'porsi_besar_protein_nilai' => $item->porsi_besar_protein_nilai ?? $item->protein_nilai,
            'porsi_besar_lemak_nilai' => $item->porsi_besar_lemak_nilai ?? $item->lemak_nilai,
            'porsi_besar_karbohidrat_nilai' => $item->porsi_besar_karbohidrat_nilai ?? $item->karbohidrat_nilai,
            'porsi_besar_serat_nilai' => $item->porsi_besar_serat_nilai ?? $item->serat_nilai,
            'porsi_kecil_energi_nilai' => $item->porsi_kecil_energi_nilai,
            'porsi_kecil_protein_nilai' => $item->porsi_kecil_protein_nilai,
            'porsi_kecil_lemak_nilai' => $item->porsi_kecil_lemak_nilai,
            'porsi_kecil_karbohidrat_nilai' => $item->porsi_kecil_karbohidrat_nilai,
            'porsi_kecil_serat_nilai' => $item->porsi_kecil_serat_nilai,
            'balita_energi_nilai' => $item->balita_energi_nilai,
            'balita_protein_nilai' => $item->balita_protein_nilai,
            'balita_lemak_nilai' => $item->balita_lemak_nilai,
            'balita_karbohidrat_nilai' => $item->balita_karbohidrat_nilai,
            'balita_serat_nilai' => $item->balita_serat_nilai,
            'bumil_busui_energi_nilai' => $item->bumil_busui_energi_nilai,
            'bumil_busui_protein_nilai' => $item->bumil_busui_protein_nilai,
            'bumil_busui_lemak_nilai' => $item->bumil_busui_lemak_nilai,
            'bumil_busui_karbohidrat_nilai' => $item->bumil_busui_karbohidrat_nilai,
            'bumil_busui_serat_nilai' => $item->bumil_busui_serat_nilai,
            'tanggal_produksi' => optional($item->tanggal_produksi)->toDateString(),
            'batas_konsumsi_dari' => substr((string) $item->batas_konsumsi, 0, 5),
            'batas_konsumsi_sampai' => substr((string) $item->batas_konsumsi_sampai, 0, 5),
        ];
    }

    private function formatNutrisi(mixed $nilai, mixed $satuan): string
    {
        $nilaiText = $nilai === null || $nilai === '' ? '-' : rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.');
        $satuanText = trim((string) ($satuan ?? ''));

        return trim($nilaiText.' '.$satuanText);
    }

    private function defaultLokasi(?ProfilMbg $profil): string
    {
        return 'SPPG SIDENRENG RAPPANG PANCA LAUTANG CORAWALI';
    }

    private function defaultPeringatan(): string
    {
        return 'Konsumsi diluar jam tersebut akan menyebabkan penurunan kualitas makanan.';
    }

    private function defaultHimbauan(): string
    {
        return 'Dilarang membawa MBG pulang ke rumah. Nikmati makanan di tempat yang disediakan agar kualitas makanan tetap terjaga.';
    }

    private function normalizeUnit(mixed $value): ?string
    {
        $unit = trim((string) ($value ?? ''));

        return $unit !== '' ? $unit : null;
    }

    private function ensureAccessible(LabelOmpreng $labelOmpreng): void
    {
        if ((int) $labelOmpreng->profil_mbg_id !== ProfilMbgTenant::id()) {
            abort(404);
        }
    }

    private function logoDataUriForPdf(?ProfilMbg $profil): ?string
    {
        $disk = Storage::disk('public');
        $relative = $profil && $profil->logo ? 'logo-mbg/'.$profil->logo : null;

        if ($relative && $disk->exists($relative)) {
            return $this->pathToDataUri($disk->path($relative));
        }

        foreach (['logo-mbg/*.png', 'logo-mbg/*.jpg', 'logo-mbg/*.jpeg', 'logo-mbg/*.webp'] as $pattern) {
            $matches = glob($disk->path($pattern)) ?: [];
            foreach ($matches as $path) {
                $dataUri = $this->pathToDataUri($path);
                if ($dataUri) {
                    return $dataUri;
                }
            }
        }

        return null;
    }

    private function pathToDataUri(string $path): ?string
    {
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }
}
