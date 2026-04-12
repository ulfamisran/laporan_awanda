@extends('layouts.app')

@section('title', 'Penggajian Relawan')

@section('content')
    @php
        $u = auth()->user();
        $isAdminDapur = $u->hasRole('admin') && ! $u->hasAnyRole(['super_admin', 'admin_pusat']);
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Penggajian relawan</h2>
            <p class="inst-page-desc">Rekap per periode, alur draft → disetujui → dibayar.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('penggajian.create', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="inst-btn-primary shrink-0">Generate penggajian</a>
        </div>
    </div>

    @if ($existingCount > 0)
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;color:#1a4a6b;">
            Periode <strong>{{ (new \App\Models\Penggajian)->forceFill(['periode_bulan' => $bulan, 'periode_tahun' => $tahun])->periode_label }}</strong> sudah memiliki
            <strong>{{ $existingCount }}</strong> entri penggajian untuk cabang ini. Generate bulk akan melewati relawan yang sudah punya record.
        </div>
    @endif

    <form method="get" action="{{ route('penggajian.index') }}" class="inst-filter-panel mb-4 space-y-3">
        <div class="flex flex-wrap gap-4">
            <div>
                <label for="f-bulan" class="inst-label-filter">Bulan</label>
                <select id="f-bulan" name="bulan" class="inst-select mt-2 w-40">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int) $bulan === $m)>
                            {{ ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$m] }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="f-tahun" class="inst-label-filter">Tahun</label>
                <input id="f-tahun" type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2100" class="inst-input mt-2 w-32">
            </div>
            @if (! $isAdminDapur)
                <div>
                    <label for="f-status" class="inst-label-filter">Status</label>
                    <select id="f-status" name="status" class="inst-select mt-2 w-44">
                        <option value="" @selected($statusFilter === '')>Semua</option>
                        <option value="draft" @selected($statusFilter === 'draft')>Draft</option>
                        <option value="approved" @selected($statusFilter === 'approved')>Disetujui</option>
                        <option value="dibayar" @selected($statusFilter === 'dibayar')>Dibayar</option>
                    </select>
                </div>
            @endif
        </div>
        <button type="submit" class="inst-btn-secondary text-sm">Terapkan filter</button>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total relawan (filter)</p>
            <p class="mt-1 text-xl font-bold" style="color:#1a4a6b;">{{ $totalRelawan }}</p>
        </div>
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total nominal</p>
            <p class="mt-1 text-xl font-bold font-mono" style="color:#2d7a60;">{{ formatRupiah($totalNominal) }}</p>
        </div>
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Sudah dibayar</p>
            <p class="mt-1 text-lg font-bold font-mono" style="color:#2d7a60;">{{ formatRupiah($sudahDibayar) }}</p>
        </div>
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Belum dibayar</p>
            <p class="mt-1 text-lg font-bold font-mono" style="color:#c0392b;">{{ formatRupiah($belumDibayar) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('penggajian.create', ['bulan' => now()->month, 'tahun' => now()->year, 'preview' => 1]) }}" class="inst-btn-secondary text-sm">Generate penggajian bulan ini</a>
        <a href="{{ route('penggajian.cetak-rekap', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="inst-btn-secondary text-sm">Cetak rekap (PDF)</a>
        <a href="{{ route('penggajian.export-excel', array_filter(['bulan' => $bulan, 'tahun' => $tahun, 'status' => $statusFilter])) }}" class="inst-btn-secondary text-sm">Export Excel</a>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Relawan</th>
                        <th>Posisi</th>
                        <th class="text-right">Gaji pokok</th>
                        <th class="text-right">Tunjangan</th>
                        <th class="text-right">Potongan</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $tunj = (float) $row->tunjangan_transport + (float) $row->tunjangan_makan + (float) $row->tunjangan_lainnya;
                            $st = $row->status instanceof \App\Enums\StatusPenggajian ? $row->status : \App\Enums\StatusPenggajian::from((string) $row->status);
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $row->relawan?->nama_lengkap ?? '—' }}</td>
                            <td class="inst-td-muted">{{ $row->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($row->gaji_pokok) }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($tunj) }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($row->potongan) }}</td>
                            <td class="text-right font-mono font-semibold">{{ formatRupiah($row->total_gaji) }}</td>
                            <td>
                                @if ($st === \App\Enums\StatusPenggajian::Draft)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#e8ecef;color:#4a5568;">Draft</span>
                                @elseif ($st === \App\Enums\StatusPenggajian::Approved)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#dbeafe;color:#1e40af;">Disetujui</span>
                                @else
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#d1fae5;color:#065f46;">Dibayar</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('penggajian.show', $row) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="inst-td-muted py-8 text-center">Tidak ada data untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
