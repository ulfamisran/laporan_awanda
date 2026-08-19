@extends('layouts.app')

@section('title', 'Detail Label Ompreng')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('label-ompreng.index') }}" class="inst-back">← Kembali</a>
            <h2 class="inst-page-title mt-2">Detail Label Ompreng</h2>
            <p class="inst-page-desc">{{ $labelOmpreng->lokasi_sppg }} — {{ $labelOmpreng->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('label-ompreng.edit', $labelOmpreng) }}" class="inst-btn-primary">Update data</a>
            <a href="{{ route('label-ompreng.create') }}" class="inst-btn-secondary">Tambah data baru</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="inst-panel p-6">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Ringkasan data</h3>
            <div class="grid gap-3 text-sm sm:grid-cols-2">
                <div><span class="font-semibold" style="color:#1a4a6b;">SPPG:</span> {{ $labelOmpreng->lokasi_sppg }}</div>
                <div><span class="font-semibold" style="color:#1a4a6b;">Format:</span> Premium Hijau Biru</div>
                <div><span class="font-semibold" style="color:#1a4a6b;">Jumlah data:</span> {{ $labelOmpreng->jumlah_item }} label</div>
                <div><span class="font-semibold" style="color:#1a4a6b;">Input oleh:</span> {{ $labelOmpreng->creator?->name ?? '—' }}</div>
            </div>

            <div class="mt-5 grid gap-4">
                <div>
                    <label class="inst-label">Peringatan</label>
                    <div class="mt-1 rounded-xl border p-3 text-sm" style="border-color:#d4e8f4;background:#fbfdff;">{{ $labelOmpreng->peringatan }}</div>
                </div>
                <div>
                    <label class="inst-label">Himbauan</label>
                    <div class="mt-1 rounded-xl border p-3 text-sm" style="border-color:#d4e8f4;background:#fbfdff;">{{ $labelOmpreng->himbauan }}</div>
                </div>
            </div>
        </div>

        <div class="inst-panel p-6">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Lihat / Cetak Label</h3>
            <form method="GET" action="{{ route('label-ompreng.preview', $labelOmpreng) }}" target="_blank" class="space-y-4">
                <input type="hidden" name="template" value="premium">
                <div>
                    <label for="jumlah_cetak" class="inst-label">Jumlah label dicetak</label>
                    <input type="number" name="jumlah_cetak" id="jumlah_cetak" min="1" max="999" value="{{ max(1, (int) $labelOmpreng->jumlah_item) }}" class="inst-input mt-1 w-full">
                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Total label mengikuti jumlah yang diisi. Setiap halaman berisi 3 label.</p>
                </div>
                <div class="rounded-xl border p-3 text-xs" style="border-color:#d4e8f4;background:#f8fbfd;color:#4a6b7f;">
                    Jika jumlah cetak lebih besar dari jumlah data label, sistem akan mengulang urutan label dari awal sampai jumlah terpenuhi.
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inst-btn-outline">Lihat Label</button>
                    <button type="submit" formaction="{{ route('label-ompreng.cetak', $labelOmpreng) }}" class="inst-btn-primary">Cetak Label</button>
                </div>
            </form>
        </div>
    </div>

    <div class="inst-panel mt-6 overflow-hidden p-4 sm:p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Daftar isi label</h3>
                <p class="text-xs" style="color:#7fa8c9;">Detail setiap label yang tersimpan dalam data ini.</p>
            </div>
            @if (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" alt="Logo BGN" style="width:52px;height:52px;object-fit:contain;">
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Menu Hari Ini</th>
                        <th>Nilai Gizi 4 Porsi</th>
                        <th>Tanggal Produksi</th>
                        <th>Batas Konsumsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($labelOmpreng->items as $item)
                        <tr>
                            <td>{{ $item->urutan }}</td>
                            <td>
                                <ol class="list-decimal space-y-1 pl-4 text-sm">
                                    <li>{{ $item->menu_1 }}</li>
                                    <li>{{ $item->menu_2 }}</li>
                                    <li>{{ $item->menu_3 }}</li>
                                    <li>{{ $item->menu_4 }}</li>
                                    <li>{{ $item->menu_5 }}</li>
                                </ol>
                            </td>
                            <td class="text-xs">
                                @php
                                    $fmt = fn($v) => $v !== null ? rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') : '-';
                                    $giziRows = [
                                        ['Energi', $item->energi_satuan ?: '', $item->porsi_besar_energi_nilai ?? $item->energi_nilai, $item->porsi_kecil_energi_nilai, $item->balita_energi_nilai, $item->bumil_busui_energi_nilai],
                                        ['Protein', $item->protein_satuan ?: '', $item->porsi_besar_protein_nilai ?? $item->protein_nilai, $item->porsi_kecil_protein_nilai, $item->balita_protein_nilai, $item->bumil_busui_protein_nilai],
                                        ['Lemak', $item->lemak_satuan ?: '', $item->porsi_besar_lemak_nilai ?? $item->lemak_nilai, $item->porsi_kecil_lemak_nilai, $item->balita_lemak_nilai, $item->bumil_busui_lemak_nilai],
                                        ['Karbo', $item->karbohidrat_satuan ?: '', $item->porsi_besar_karbohidrat_nilai ?? $item->karbohidrat_nilai, $item->porsi_kecil_karbohidrat_nilai, $item->balita_karbohidrat_nilai, $item->bumil_busui_karbohidrat_nilai],
                                        ['Serat', $item->serat_satuan ?: '', $item->porsi_besar_serat_nilai ?? $item->serat_nilai, $item->porsi_kecil_serat_nilai, $item->balita_serat_nilai, $item->bumil_busui_serat_nilai],
                                    ];
                                @endphp
                                <table class="w-full text-xs" style="border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#eef6fb;">
                                            <th class="px-1.5 py-1 text-left font-bold" style="color:#1a4a6b;"></th>
                                            <th class="px-1.5 py-1 text-center font-bold" style="color:#1a4a6b;">Besar</th>
                                            <th class="px-1.5 py-1 text-center font-bold" style="color:#1a4a6b;">Kecil</th>
                                            <th class="px-1.5 py-1 text-center font-bold" style="color:#1a4a6b;">Balita</th>
                                            <th class="px-1.5 py-1 text-center font-bold" style="color:#1a4a6b;">Bumil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($giziRows as $r)
                                            <tr style="border-top:1px solid #e8f1f8;">
                                                <td class="px-1.5 py-1 font-semibold" style="color:#334155;">{{ $r[0] }}</td>
                                                <td class="px-1.5 py-1 text-center">{{ $fmt($r[2]) }} {{ $r[1] }}</td>
                                                <td class="px-1.5 py-1 text-center">{{ $fmt($r[3]) }} {{ $r[1] }}</td>
                                                <td class="px-1.5 py-1 text-center">{{ $fmt($r[4]) }} {{ $r[1] }}</td>
                                                <td class="px-1.5 py-1 text-center">{{ $fmt($r[5]) }} {{ $r[1] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                            <td>{{ $item->tanggal_produksi?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ substr((string) $item->batas_konsumsi, 0, 5) }} - {{ substr((string) $item->batas_konsumsi_sampai, 0, 5) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
