@php
    $current = $tab ?? 'stok';
    $linkClass = function (string $t) use ($current) {
        $base = 'rounded-lg border px-3 py-2 text-sm font-medium transition ';
        if ($t === $current) {
            return $base.'border-[#4a9b7a] bg-white text-[#1a4a6b] shadow-sm';
        }

        return $base.'border-transparent text-[#4a6b7f] hover:border-[#d4e8f4] hover:bg-white/60';
    };
@endphp
<nav class="mb-6 flex flex-wrap gap-2 rounded-xl border border-[#d4e8f4] bg-[#f0f6fb] p-2" aria-label="Jenis laporan">
    <a href="{{ route('laporan-rekap.stok') }}" class="{{ $linkClass('stok') }}">Stok barang</a>
    <a href="{{ route('laporan-rekap.arus') }}" class="{{ $linkClass('arus') }}">Arus stok detail</a>
    <a href="{{ route('laporan-rekap.keuangan') }}" class="{{ $linkClass('keuangan') }}">Keuangan</a>
    <a href="{{ route('laporan-rekap.penggajian') }}" class="{{ $linkClass('penggajian') }}">Penggajian</a>
    <a href="{{ route('laporan-rekap.limbah') }}" class="{{ $linkClass('limbah') }}">Limbah</a>
    <a href="{{ route('laporan-rekap.komprehensif') }}" class="{{ $linkClass('komprehensif') }}">Komprehensif</a>
</nav>
