@extends('layouts.app')

@section('title', 'Ubah Komponen Gaji')

@section('content')
    @php
        $p = $penggajian;
        $gaji = (float) $p->gaji_pokok;
        $t1 = (float) $p->tunjangan_transport;
        $t2 = (float) $p->tunjangan_makan;
        $t3 = (float) $p->tunjangan_lainnya;
        $pot = (float) $p->potongan;
        $totalInit = $gaji + $t1 + $t2 + $t3 - $pot;
    @endphp

    <div class="mb-6">
        <h2 class="inst-page-title">Komponen gaji</h2>
        <p class="inst-page-desc">{{ $p->relawan?->nama_lengkap }} — {{ $p->periode_label }} (tidak dapat diubah).</p>
    </div>

    <form method="post" action="{{ route('penggajian.update', $p) }}" class="inst-panel max-w-2xl space-y-5 p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-2 rounded-xl border p-4 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
            <p><span class="font-semibold" style="color:#1a4a6b;">Relawan:</span> {{ $p->relawan?->nama_lengkap }}</p>
            <p><span class="font-semibold" style="color:#1a4a6b;">Periode:</span> {{ $p->periode_label }}</p>
            <p><span class="font-semibold" style="color:#1a4a6b;">Dapur:</span> {{ $p->profilMbg?->nama_dapur }}</p>
        </div>

        <div>
            <label class="inst-label">Gaji pokok</label>
            <input type="text" class="inst-input mt-1 w-full bg-gray-50" readonly value="{{ formatRupiah($p->gaji_pokok) }}">
        </div>

        <div>
            <label for="tunjangan_transport" class="inst-label">Tunjangan transport</label>
            <input type="number" step="0.01" min="0" name="tunjangan_transport" id="tunjangan_transport" class="inst-input mt-1 w-full js-gaji"
                value="{{ old('tunjangan_transport', $p->tunjangan_transport) }}" required>
        </div>
        <div>
            <label for="tunjangan_makan" class="inst-label">Tunjangan makan</label>
            <input type="number" step="0.01" min="0" name="tunjangan_makan" id="tunjangan_makan" class="inst-input mt-1 w-full js-gaji"
                value="{{ old('tunjangan_makan', $p->tunjangan_makan) }}" required>
        </div>
        <div>
            <label for="tunjangan_lainnya" class="inst-label">Tunjangan lainnya</label>
            <input type="number" step="0.01" min="0" name="tunjangan_lainnya" id="tunjangan_lainnya" class="inst-input mt-1 w-full js-gaji"
                value="{{ old('tunjangan_lainnya', $p->tunjangan_lainnya) }}" required>
        </div>
        <div>
            <label for="potongan" class="inst-label">Potongan</label>
            <input type="number" step="0.01" min="0" name="potongan" id="potongan" class="inst-input mt-1 w-full js-gaji"
                value="{{ old('potongan', $p->potongan) }}" required>
        </div>
        <div>
            <label for="keterangan_potongan" class="inst-label">Keterangan potongan</label>
            <input type="text" name="keterangan_potongan" id="keterangan_potongan" class="inst-input mt-1 w-full"
                value="{{ old('keterangan_potongan', $p->keterangan_potongan) }}" maxlength="500">
        </div>
        <div>
            <label for="catatan" class="inst-label">Catatan penggajian</label>
            <textarea name="catatan" id="catatan" rows="3" class="inst-input mt-1 w-full">{{ old('catatan', $p->catatan) }}</textarea>
        </div>

        <div class="rounded-xl border p-4" style="border-color:#4a9b7a;background:#f0fdf4;">
            <p class="text-xs font-bold uppercase" style="color:#2d7a60;">Pratinjau total gaji</p>
            <p id="preview-total" class="mt-1 text-xl font-bold font-mono" style="color:#1a4a6b;">{{ formatRupiah($totalInit) }}</p>
            <p class="mt-1 text-xs inst-td-muted">Dihitung otomatis saat simpan (gaji pokok + tunjangan − potongan).</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="inst-btn-primary">Simpan</button>
            <a href="{{ route('penggajian.show', $p) }}" class="inst-btn-secondary">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const gajiPokok = {{ json_encode($gaji) }};
            const inputs = document.querySelectorAll('.js-gaji');
            const out = document.getElementById('preview-total');
            function num(el) {
                const v = parseFloat(String(el.value).replace(',', '.'));
                return Number.isFinite(v) ? v : 0;
            }
            function fmt(n) {
                const x = Math.round((n + Number.EPSILON) * 100) / 100;
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 }).format(x);
            }
            function recalc() {
                const t = num(document.getElementById('tunjangan_transport'));
                const m = num(document.getElementById('tunjangan_makan'));
                const l = num(document.getElementById('tunjangan_lainnya'));
                const p = num(document.getElementById('potongan'));
                const total = gajiPokok + t + m + l - p;
                if (out) out.textContent = fmt(total);
            }
            inputs.forEach((el) => el.addEventListener('input', recalc));
            recalc();
        })();
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
