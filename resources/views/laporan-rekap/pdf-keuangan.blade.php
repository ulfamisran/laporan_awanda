@extends('laporan-rekap.pdf.layout')

@section('pdf_body')
    <table class="data" style="margin-bottom: 12px;">
        <tbody>
            <tr>
                <th style="width: 40%;">Saldo awal periode</th>
                <td class="num">{{ number_format((float) ($data['saldo_awal'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total masuk</th>
                <td class="num">{{ number_format((float) ($data['total_masuk_periode'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total keluar</th>
                <td class="num">{{ number_format((float) ($data['total_keluar_periode'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Saldo akhir</th>
                <td class="num"><strong>{{ number_format((float) ($data['saldo_akhir'] ?? 0), 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
    <h2 class="section">Rincian dana masuk per jenis dana</h2>
    <table class="data">
        <thead><tr><th>Akun (Jenis Dana)</th><th class="num">Jumlah</th></tr></thead>
        <tbody>
            @forelse ($data['masuk_per_jenis_dana'] ?? [] as $row)
                <tr><td>{{ $row['nama'] }}</td><td class="num">{{ number_format((float) $row['total'], 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="2">Tidak ada</td></tr>
            @endforelse
        </tbody>
    </table>
    <h2 class="section">Rincian dana keluar per jenis dana</h2>
    <table class="data">
        <thead><tr><th>Akun (Jenis Dana)</th><th class="num">Jumlah</th></tr></thead>
        <tbody>
            @forelse ($data['keluar_per_jenis_dana'] ?? [] as $row)
                <tr><td>{{ $row['nama'] }}</td><td class="num">{{ number_format((float) $row['total'], 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="2">Tidak ada</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
