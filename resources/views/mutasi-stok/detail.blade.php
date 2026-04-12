@extends('layouts.app')

@section('title', 'Riwayat Mutasi — '.$barang->nama_barang)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('stok.mutasi.index') }}" class="inst-back">← Rekap mutasi</a>
            <h2 class="inst-page-title mt-2">Riwayat mutasi</h2>
            <p class="font-mono text-sm" style="color:#4a6b7f;">{{ $barang->kode_barang }} — {{ $barang->nama_barang }}</p>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kode</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Saldo</th>
                        <th>Keterangan</th>
                        <th>Input oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        @php
                            $tgl = $r['tanggal'] instanceof \DateTimeInterface
                                ? $r['tanggal']->format('d/m/Y')
                                : \Illuminate\Support\Carbon::parse($r['tanggal'])->format('d/m/Y');
                            $qty = ($r['arah'] ?? 0) * (float) ($r['jumlah'] ?? 0);
                            $qtyClass = $qty < 0 ? 'text-rose-700' : ($qty > 0 ? 'text-emerald-700' : '');
                        @endphp
                        <tr>
                            <td>{{ $tgl }}</td>
                            <td>{{ $r['label'] ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $r['kode'] ?? '—' }}</td>
                            <td class="text-right font-mono {{ $qtyClass }}">
                                @if ($qty > 0)
                                    +{{ number_format((float) $r['jumlah'], 2, ',', '.') }}
                                @elseif ($qty < 0)
                                    −{{ number_format((float) $r['jumlah'], 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right font-mono font-semibold">{{ number_format((float) ($r['saldo'] ?? 0), 2, ',', '.') }}</td>
                            <td class="max-w-xs truncate text-sm">{{ $r['keterangan'] ?? '—' }}</td>
                            <td class="text-sm">{{ $r['oleh'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-sm" style="color:#7fa8c9;">
                                Belum ada transaksi stok untuk barang ini di dapur ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
