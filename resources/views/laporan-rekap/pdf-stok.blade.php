@extends('laporan-rekap.pdf.layout')

@section('pdf_body')
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama barang</th>
                <th>Kategori</th>
                <th class="num">Stok awal</th>
                <th class="num">Masuk</th>
                <th class="num">Keluar</th>
                <th class="num">Saldo akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->kode }}</td>
                    <td>{{ $r->nama }}</td>
                    <td>{{ $r->kategori }}</td>
                    <td class="num">{{ number_format((float) $r->stok_awal, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->masuk, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->keluar, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->saldo_akhir, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
