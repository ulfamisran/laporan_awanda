@extends('layouts.app')

@section('title', 'Detail Batch Penggajian')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Detail batch penggajian</h2>
            <p class="inst-page-desc">{{ $periodeLabel }} — {{ $metode === 'kehadiran' ? 'Berdasarkan kehadiran' : 'Berdasarkan gaji pokok' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('penggajian.index') }}" class="inst-btn-secondary">Kembali</a>
            <a href="{{ route('penggajian.cetak-kwitansi-batch', ['mulai' => $mulai, 'selesai' => $selesai, 'metode' => $metode]) }}" target="_blank" class="inst-btn-secondary">Print kwitansi batch</a>
        </div>
    </div>

    <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;color:#1a4a6b;">
        Total relawan: <strong>{{ $rows->count() }}</strong> &nbsp;|&nbsp; Total pembayaran: <strong>{{ formatRupiah($totalPembayaran) }}</strong>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Relawan</th>
                        <th>Posisi</th>
                        <th class="text-right">Jumlah hadir</th>
                        <th class="text-right">Gaji dasar</th>
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
                            <td>{{ $row->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
                            <td class="text-right">{{ $row->jumlah_hadir }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($row->gaji_pokok) }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($tunj) }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($row->potongan) }}</td>
                            <td class="text-right font-mono font-semibold">{{ formatRupiah($row->total_gaji) }}</td>
                            <td class="text-xs">{{ $st->label() }}</td>
                            <td class="text-right"><a href="{{ route('penggajian.show', $row) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="inst-td-muted py-8 text-center">Tidak ada data relawan pada batch ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
