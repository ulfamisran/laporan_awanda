@extends('laporan-rekap.pdf.layout')

@section('pdf_body')
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th class="num">Total (kg est.)</th>
                <th class="num">Dibuang</th>
                <th class="num">Daur ulang</th>
                <th class="num">Dijual (kg)</th>
                <th class="num">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->nama_kategori }}</td>
                    <td class="num">{{ number_format((float) $r->total_kg, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->dibuang, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->daur, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->dijual, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->pendapatan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada data limbah pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
