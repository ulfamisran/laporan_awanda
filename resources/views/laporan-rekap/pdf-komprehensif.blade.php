@extends('laporan-rekap.pdf.layout')

@section('pdf_body')
    <p style="font-size: 9px; color: #4a6b7f; margin: 0 0 12px 0;">
        Dokumen ini menyatukan ringkasan stok barang, keuangan (neraca bulan akhir periode), penggajian, dan limbah untuk pelaporan ke pusat.
    </p>

    <h2 class="section">1. Stok barang (rekap periode)</h2>
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th class="num">Awal</th>
                <th class="num">Masuk</th>
                <th class="num">Keluar</th>
                <th class="num">Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stokRows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->kode }}</td>
                    <td>{{ $r->nama }}</td>
                    <td class="num">{{ number_format((float) $r->stok_awal, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->masuk, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->keluar, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->saldo_akhir, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section page-break">2. Keuangan — neraca (bulan akhir periode: {{ $neraca['mulai']->translatedFormat('F Y') }})</h2>
    <table class="data" style="margin-bottom: 8px;">
        <tbody>
            <tr><th>Saldo awal</th><td class="num">{{ number_format((float) ($neraca['saldo_awal'] ?? 0), 0, ',', '.') }}</td></tr>
            <tr><th>Total masuk</th><td class="num">{{ number_format((float) ($neraca['total_masuk_periode'] ?? 0), 0, ',', '.') }}</td></tr>
            <tr><th>Total keluar</th><td class="num">{{ number_format((float) ($neraca['total_keluar_periode'] ?? 0), 0, ',', '.') }}</td></tr>
            <tr><th>Saldo akhir</th><td class="num"><strong>{{ number_format((float) ($neraca['saldo_akhir'] ?? 0), 0, ',', '.') }}</strong></td></tr>
        </tbody>
    </table>
    <table class="data" style="width: 49%; display: inline-table; vertical-align: top; margin-right: 1%;">
        <thead><tr><th colspan="2">Dana masuk per jenis dana</th></tr></thead>
        <tbody>
            @foreach ($neraca['masuk_per_jenis_dana'] ?? [] as $row)
                <tr><td>{{ $row['nama'] }}</td><td class="num">{{ number_format((float) $row['total'], 0, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <table class="data" style="width: 49%; display: inline-table; vertical-align: top;">
        <thead><tr><th colspan="2">Dana keluar per jenis dana</th></tr></thead>
        <tbody>
            @foreach ($neraca['keluar_per_jenis_dana'] ?? [] as $row)
                <tr><td>{{ $row['nama'] }}</td><td class="num">{{ number_format((float) $row['total'], 0, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section page-break">3. Penggajian (rentang periode)</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Periode</th>
                <th>Nama</th>
                <th>Posisi</th>
                <th class="num">Total gaji</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengRows as $r)
                @php
                    $pl = str_pad((string) $r->periode_bulan, 2, '0', STR_PAD_LEFT).'/'.$r->periode_tahun;
                    $st = $r->status instanceof \App\Enums\StatusPenggajian ? $r->status->label() : (string) $r->status;
                @endphp
                <tr>
                    <td>{{ $pl }}</td>
                    <td>{{ $r->relawan?->nama_lengkap }}</td>
                    <td>{{ $r->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
                    <td class="num">{{ number_format((float) $r->total_gaji, 0, ',', '.') }}</td>
                    <td>{{ $st }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada penggajian pada rentang periode.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section page-break">4. Limbah (rekap kategori)</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="num">Total kg</th>
                <th class="num">Dibuang</th>
                <th class="num">Daur</th>
                <th class="num">Dijual</th>
                <th class="num">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($limbahRows as $r)
                <tr>
                    <td>{{ $r->nama_kategori }}</td>
                    <td class="num">{{ number_format((float) $r->total_kg, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->dibuang, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->daur, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->dijual, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->pendapatan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data limbah.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
