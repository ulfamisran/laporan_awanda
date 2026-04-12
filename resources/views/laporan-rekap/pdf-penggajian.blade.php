@extends('laporan-rekap.pdf.layout')

@section('pdf_body')
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Posisi</th>
                <th class="num">Gaji pokok</th>
                <th class="num">Tunjangan</th>
                <th class="num">Potongan</th>
                <th class="num">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $r)
                @php
                    $tunj = (float) $r->tunjangan_transport + (float) $r->tunjangan_makan + (float) $r->tunjangan_lainnya;
                    $st = $r->status instanceof \App\Enums\StatusPenggajian ? $r->status->label() : (string) $r->status;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->relawan?->nama_lengkap }}</td>
                    <td>{{ $r->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
                    <td class="num">{{ number_format((float) $r->gaji_pokok, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($tunj, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->potongan, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->total_gaji, 0, ',', '.') }}</td>
                    <td>{{ $st }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold; background: #f0fdf4;">
                <td colspan="6" style="text-align: right;">Total pengeluaran gaji</td>
                <td class="num">{{ number_format((float) $totalKeseluruhan, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
@endsection
