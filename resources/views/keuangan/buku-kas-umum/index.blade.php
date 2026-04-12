@extends('layouts.app')

@section('title', 'Buku Kas Umum')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Buku kas umum</h2>
            <p class="inst-page-desc">
                Periode: {{ $periode->labelRingkas() }}
                @if ($periode->tanggal_pelaporan)
                    · Tgl. pelaporan {{ $periode->tanggal_pelaporan->format('d/m/Y') }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('keuangan.transaksi.index') }}" class="inst-btn-outline shrink-0">Transaksi</a>
            <a href="{{ route('keuangan.masuk.create') }}" class="inst-btn-outline shrink-0">Dana masuk</a>
            <a href="{{ route('keuangan.keluar.create') }}" class="inst-btn-outline shrink-0">Dana keluar</a>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="stat-card rounded-2xl p-5" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Saldo awal</p>
            <p class="mt-2 text-xl font-bold font-mono tracking-tight" style="color:#1a4a6b;">{{ formatRupiah($saldoAwal) }}</p>
            <p class="mt-2 text-xs" style="color:#7fa8c9;">Posisi sesaat sebelum {{ $periode->tanggal_awal->format('d/m/Y') }} (stok dana awal + transaksi s/d hari sebelumnya).</p>
        </div>
        <div class="stat-card rounded-2xl p-5" style="background:linear-gradient(135deg,#ecfdf5,#d4f0e8);border-color:#4a9b7a;">
            <p class="text-xs font-bold uppercase" style="color:#2d7a60;">Saldo akhir</p>
            <p class="mt-2 text-xl font-bold font-mono tracking-tight" style="color:#14532d;">{{ formatRupiah($saldoAkhir) }}</p>
            <p class="mt-2 text-xs" style="color:#2d7a60;">Setelah semua transaksi periode ini (masuk − keluar).</p>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <h3 class="mb-4 text-sm font-bold uppercase" style="color:#7fa8c9;">Jurnal periode</h3>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Tgl</th>
                        <th>No. bukti</th>
                        <th>Uraian transaksi</th>
                        <th class="text-right">Debet</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($baris as $b)
                        <tr>
                            <td>{{ $b['tanggal']?->format('d/m/Y') ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $b['nomor_bukti'] !== '' ? $b['nomor_bukti'] : '—' }}</td>
                            <td class="max-w-md">{{ $b['uraian_transaksi'] !== '' ? $b['uraian_transaksi'] : '—' }}</td>
                            <td class="text-right font-mono text-sm">
                                @if ($b['debet'] > 0)
                                    {{ formatRupiah($b['debet']) }}
                                @else
                                    <span class="inst-td-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right font-mono text-sm">
                                @if ($b['kredit'] > 0)
                                    {{ formatRupiah($b['kredit']) }}
                                @else
                                    <span class="inst-td-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right font-mono text-sm font-semibold" style="color:#1a4a6b;">{{ formatRupiah($b['saldo']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm inst-td-muted">Belum ada transaksi dana pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
