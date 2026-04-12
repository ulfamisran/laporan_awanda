@extends('laporan-rekap.pdf.layout')

@section('pdf_body')
    <p style="margin: 0 0 8px 0; font-size: 9px; color: #4a6b7f;"><strong>Barang:</strong> {{ $namaBarang ?? '—' }}</p>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th class="num">Qty</th>
                <th>Arah</th>
                <th>Keterangan</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $r['tanggal'] ?? '—' }}</td>
                    <td>{{ $r['jenis'] ?? '' }}</td>
                    <td class="num">{{ number_format((float) ($r['qty'] ?? 0), 2, ',', '.') }}</td>
                    <td>{{ $r['arah'] ?? '' }}</td>
                    <td>{{ $r['keterangan'] ?? '' }}</td>
                    <td>{{ $r['oleh'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada mutasi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
