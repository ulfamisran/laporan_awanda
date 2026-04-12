@extends('layouts.app')

@section('title', 'Laporan penggajian')

@section('content')
    @include('laporan-rekap._tabs', ['tab' => 'penggajian'])

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan penggajian</h2>
            <p class="inst-page-desc">
                {{ \Illuminate\Support\Carbon::createFromDate((int) $f['tahun'], (int) $f['bulan'], 1)->translatedFormat('F Y') }}
                — {{ $namaDapur }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-rekap.penggajian.export-excel') }}" class="inst-btn-outline">Excel</a>
            <a href="{{ route('laporan-rekap.penggajian.export-pdf') }}" target="_blank" class="inst-btn-outline">PDF</a>
            <a href="{{ route('laporan-rekap.penggajian.export-pdf', ['draft' => 1]) }}" target="_blank" class="inst-btn-outline">PDF (draft)</a>
        </div>
    </div>

    <form method="GET" action="{{ route('laporan-rekap.penggajian') }}" class="inst-panel mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5 sm:p-6">
        <div>
            <label for="bulan" class="inst-label-filter">Bulan</label>
            <select name="bulan" id="bulan" class="inst-select mt-2">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected((int) $f['bulan'] === $m)>{{ \Illuminate\Support\Carbon::create(2000, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="tahun" class="inst-label-filter">Tahun</label>
            <input type="number" name="tahun" id="tahun" class="inst-input mt-2" min="2000" max="2100" value="{{ $f['tahun'] }}">
        </div>
        <div>
            <label for="status" class="inst-label-filter">Status</label>
            <select name="status" id="status" class="inst-select mt-2">
                <option value="" @selected($f['status'] === '' || $f['status'] === null)>Semua</option>
                @foreach (\App\Enums\StatusPenggajian::cases() as $st)
                    <option value="{{ $st->value }}" @selected((string) $f['status'] === $st->value)>{{ $st->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="inst-btn-primary">Terapkan</button>
        </div>
    </form>

    <div class="inst-panel mb-4 p-4 sm:p-6">
        <p class="text-sm" style="color:#7fa8c9;">Total pengeluaran gaji (filter)</p>
        <p class="font-mono text-2xl font-bold" style="color:#1a4a6b;">{{ formatRupiah($totalGaji) }}</p>
    </div>

    <div class="inst-panel overflow-x-auto p-4 sm:p-6">
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
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    @php
                        $tunj = (float) $r->tunjangan_transport + (float) $r->tunjangan_makan + (float) $r->tunjangan_lainnya;
                        $st = $r->status instanceof \App\Enums\StatusPenggajian ? $r->status->label() : (string) $r->status;
                    @endphp
                    <tr>
                        <td>{{ $r->relawan?->nama_lengkap }}</td>
                        <td>{{ $r->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
                        <td class="text-right font-mono">{{ formatRupiah($r->gaji_pokok) }}</td>
                        <td class="text-right font-mono">{{ formatRupiah($tunj) }}</td>
                        <td class="text-right font-mono">{{ formatRupiah($r->potongan) }}</td>
                        <td class="text-right font-mono font-semibold">{{ formatRupiah($r->total_gaji) }}</td>
                        <td>{{ $st }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-sm" style="color:#7fa8c9;">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
