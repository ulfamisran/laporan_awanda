@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Ubah Permintaan Insentif' : 'Buat Permintaan Insentif')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="inst-page-title">{{ $mode === 'edit' ? 'Ubah permintaan insentif' : 'Buat permintaan insentif' }}</h2>
            <p class="inst-page-desc">Isi formulir seperti surat. Pratinjau mengikuti format PDF resmi.</p>
        </div>
        <a href="{{ route('permintaan-insentif.index') }}" class="inst-btn-outline">Kembali</a>
    </div>

    @include('components.periode-aktif-badge')

    @if ($errors->any())
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post"
        action="{{ $mode === 'edit' ? route('permintaan-insentif.update', $item) : route('permintaan-insentif.store') }}"
        class="mx-auto max-w-4xl">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <div class="overflow-hidden rounded-sm border bg-white shadow-sm" style="border-color:#cbd5e1;">
            {{-- Pratinjau KOP --}}
            <div class="border-b px-6 py-5 text-center" style="border-color:#1a4a6b;color:#1a4a6b;">
                @php
                    $logoSrc = $logoDataUri ?? ($profil?->logo_url);
                @endphp
                @if (! empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo SPPG" class="mx-auto mb-3 max-h-20 object-contain">
                @endif
                <div class="text-sm font-bold">BADAN GIZI NASIONAL (<em>NATIONAL NUTRITION AGENCY</em>)</div>
                <div class="text-sm font-bold">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)</div>
                <div class="text-sm font-bold">{{ $profil->daerah_sppg ?: $profil->nama_dapur }}</div>
                <div class="text-xs">{{ $profil->alamat ?: '—' }}</div>
            </div>

            <div class="space-y-5 px-6 py-6 text-sm" style="color:#1f2937;">
                <div>
                    <label class="inst-label mb-1 block">Judul surat</label>
                    <input type="text" name="judul_surat" value="{{ old('judul_surat', $item->judul_surat) }}"
                        class="inst-input w-full text-center text-base font-bold uppercase tracking-wide" required>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="inst-label mb-1 block">Periode</label>
                        <input type="text" name="periode_label" value="{{ old('periode_label', $item->periode_label) }}"
                            class="inst-input w-full" placeholder="12-23 Oktober 2026" required>
                    </div>
                    <div>
                        <label class="inst-label mb-1 block">Program</label>
                        <input type="text" name="program" id="program" value="{{ old('program', $item->program) }}"
                            class="inst-input w-full" required>
                    </div>
                </div>

                <div>
                    <p class="mb-2 font-medium">Yang Bertanda Tangan di bawah ini :</p>
                    <div class="grid gap-3 sm:grid-cols-[7rem_1rem_1fr] sm:items-center">
                        <span>SPPG</span><span>:</span>
                        <input type="text" name="nama_sppg" value="{{ old('nama_sppg', $item->nama_sppg) }}" class="inst-input w-full" required>
                        <span>Nama</span><span>:</span>
                        <input type="text" name="nama_peminta" value="{{ old('nama_peminta', $item->nama_peminta) }}" class="inst-input w-full" required>
                        <span>Jabatan</span><span>:</span>
                        <input type="text" name="jabatan_peminta" value="{{ old('jabatan_peminta', $item->jabatan_peminta) }}" class="inst-input w-full" required>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="inst-label mb-1 block">Tanggal dalam perihal</label>
                        <input type="date" name="tanggal_perihal" id="tanggal_perihal"
                            value="{{ old('tanggal_perihal', optional($item->tanggal_perihal)->format('Y-m-d')) }}"
                            class="inst-input w-full" required>
                    </div>
                    <div>
                        <label class="inst-label mb-1 block">Jumlah (Rp)</label>
                        <input type="number" step="0.01" min="0" name="jumlah" id="jumlah"
                            value="{{ old('jumlah', $item->jumlah) }}"
                            class="inst-input w-full font-mono" required>
                    </div>
                </div>

                @php
                    $programVal = old('program', $item->program);
                    $tglPerihalVal = old('tanggal_perihal', optional($item->tanggal_perihal)?->format('Y-m-d'));
                    $tglPerihalLabel = $tglPerihalVal
                        ? \Carbon\Carbon::parse($tglPerihalVal)->translatedFormat('d F Y')
                        : '—';
                    $jumlahVal = (float) old('jumlah', $item->jumlah ?? 0);
                    $jumlahLabel = 'Rp. '.number_format($jumlahVal, 0, ',', '.');
                    $perihalOtomatis = 'Dengan ini mengajukan Pembayaran Insentif PIC Sekolah untuk Operasional SPPG pada Program '.$programVal.' '.$tglPerihalLabel.' sebesar '.$jumlahLabel.'.';
                    $perihalTampil = old('perihal_teks', $item->perihal_teks);
                    if (trim((string) $perihalTampil) === '') {
                        $perihalTampil = $perihalOtomatis;
                    }
                @endphp

                <div>
                    <label class="inst-label mb-1 block">Teks perihal</label>
                    <textarea name="perihal_teks" id="perihal_teks" rows="3" class="inst-input w-full"
                        data-auto="1">{{ $perihalTampil }}</textarea>
                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Teks diisi otomatis dari program, tanggal, dan jumlah. Bisa diedit manual.</p>
                </div>

                <div>
                    <label class="inst-label mb-1 block">Catatan</label>
                    <textarea name="catatan" rows="3" class="inst-input w-full">{{ old('catatan', $item->catatan) }}</textarea>
                </div>

                <div class="border-t pt-5" style="border-color:#e2e8f0;">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wide" style="color:#1a4a6b;">Tanda tangan</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="inst-label mb-1 block">Tempat TTD</label>
                            <input type="text" name="tempat_ttd" value="{{ old('tempat_ttd', $item->tempat_ttd) }}" class="inst-input w-full">
                        </div>
                        <div>
                            <label class="inst-label mb-1 block">Tanggal TTD</label>
                            <input type="date" name="tanggal_ttd" value="{{ old('tanggal_ttd', optional($item->tanggal_ttd)->format('Y-m-d') ?: optional($item->tanggal_perihal)->format('Y-m-d')) }}" class="inst-input w-full">
                        </div>
                        <div>
                            <label class="inst-label mb-1 block">Jabatan Mengetahui</label>
                            <input type="text" name="ttd_mengetahui_jabatan" value="{{ old('ttd_mengetahui_jabatan', $item->ttd_mengetahui_jabatan) }}" class="inst-input w-full">
                        </div>
                        <div>
                            <label class="inst-label mb-1 block">Nama Mengetahui</label>
                            <input type="text" name="ttd_mengetahui_nama" value="{{ old('ttd_mengetahui_nama', $item->ttd_mengetahui_nama) }}" class="inst-input w-full">
                        </div>
                        <div>
                            <label class="inst-label mb-1 block">Jabatan Menyetujui</label>
                            <input type="text" name="ttd_menyetujui_jabatan" value="{{ old('ttd_menyetujui_jabatan', $item->ttd_menyetujui_jabatan) }}" class="inst-input w-full">
                        </div>
                        <div>
                            <label class="inst-label mb-1 block">Nama Menyetujui</label>
                            <input type="text" name="ttd_menyetujui_nama" value="{{ old('ttd_menyetujui_nama', $item->ttd_menyetujui_nama) }}" class="inst-input w-full">
                        </div>
                    </div>
                    <p class="mt-3 text-xs" style="color:#7fa8c9;">Pemohon memakai Nama &amp; Jabatan peminta di atas.</p>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="submit" class="inst-btn-primary">{{ $mode === 'edit' ? 'Simpan perubahan' : 'Simpan permintaan' }}</button>
            <a href="{{ route('permintaan-insentif.index') }}" class="inst-btn-outline">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const programEl = document.getElementById('program');
    const tanggalEl = document.getElementById('tanggal_perihal');
    const jumlahEl = document.getElementById('jumlah');
    const perihalEl = document.getElementById('perihal_teks');
    if (!programEl || !tanggalEl || !jumlahEl || !perihalEl) return;

    let lastAuto = perihalEl.value;
    let isAuto = perihalEl.getAttribute('data-auto') === '1';

    function formatRp(n) {
        const num = Math.round(Number(n) || 0);
        return 'Rp. ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatTgl(iso) {
        if (!iso) return '—';
        const p = iso.split('-');
        if (p.length !== 3) return iso;
        const d = parseInt(p[2], 10);
        const m = parseInt(p[1], 10) - 1;
        return d + ' ' + (bulan[m] || '') + ' ' + p[0];
    }

    function buildAuto() {
        return 'Dengan ini mengajukan Pembayaran Insentif PIC Sekolah untuk Operasional SPPG pada Program '
            + (programEl.value || '—') + ' ' + formatTgl(tanggalEl.value)
            + ' sebesar ' + formatRp(jumlahEl.value) + '.';
    }

    function syncAuto() {
        const next = buildAuto();
        if (isAuto && perihalEl.value === lastAuto) {
            perihalEl.value = next;
            lastAuto = next;
        }
    }

    perihalEl.addEventListener('input', function () {
        isAuto = perihalEl.value === lastAuto || perihalEl.value === buildAuto();
        if (isAuto) lastAuto = perihalEl.value;
    });

    ['input', 'change'].forEach(function (evt) {
        programEl.addEventListener(evt, syncAuto);
        tanggalEl.addEventListener(evt, syncAuto);
        jumlahEl.addEventListener(evt, syncAuto);
    });
})();
</script>
@endpush
