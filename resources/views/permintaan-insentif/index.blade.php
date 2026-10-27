@extends('layouts.app')

@section('title', 'Permintaan Insentif')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="inst-page-title">Permintaan Insentif</h2>
            <p class="inst-page-desc">Buat dan cetak surat permintaan pembayaran insentif PIC Sekolah.</p>
        </div>
        <a href="{{ route('permintaan-insentif.create') }}" class="inst-btn-primary">Buat permintaan</a>
    </div>

    @include('components.periode-aktif-badge')

    @if (session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
            {{ session('success') }}
        </div>
    @endif

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table w-full" style="min-width: 56rem; table-layout: fixed;">
                <colgroup>
                    <col style="width: 28%;">
                    <col style="width: 16%;">
                    <col style="width: 14%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 18%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="pr-4">Judul</th>
                        <th class="pr-4">Periode</th>
                        <th class="pr-4">Peminta</th>
                        <th class="pr-4 text-right">Jumlah</th>
                        <th class="pr-4">Tanggal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="pr-4 font-medium" style="white-space: normal; word-break: break-word;">
                                {{ $row->judul_surat }}
                            </td>
                            <td class="pr-4" style="white-space: normal;">{{ $row->periode_label }}</td>
                            <td class="pr-4">
                                <div class="font-medium">{{ $row->nama_peminta }}</div>
                                <div class="text-xs" style="color:#7fa8c9;">{{ $row->jabatan_peminta }}</div>
                            </td>
                            <td class="pr-4 text-right font-mono font-semibold whitespace-nowrap">
                                {{ formatRupiah($row->jumlah) }}
                            </td>
                            <td class="pr-4 whitespace-nowrap">
                                {{ optional($row->tanggal_perihal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                    <a href="{{ route('permintaan-insentif.show', $row) }}" class="text-xs font-semibold whitespace-nowrap" style="color:#1a4a6b;">Detail</a>
                                    <a href="{{ route('permintaan-insentif.edit', $row) }}" class="text-xs font-semibold whitespace-nowrap" style="color:#4a9b7a;">Ubah</a>
                                    <a href="{{ route('permintaan-insentif.cetak-pdf', $row) }}" target="_blank" class="text-xs font-semibold whitespace-nowrap" style="color:#0f766e;">Cetak PDF</a>
                                    <form method="post" action="{{ route('permintaan-insentif.destroy', $row) }}" class="inline" onsubmit="return confirm('Hapus permintaan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold whitespace-nowrap" style="color:#c0392b;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="inst-td-muted py-10 text-center">Belum ada permintaan insentif pada periode aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rows->hasPages())
            <div class="border-t px-1 pt-3 mt-2" style="border-color:#d4e8f4;">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
